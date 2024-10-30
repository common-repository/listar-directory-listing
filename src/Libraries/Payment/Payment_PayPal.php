<?php
namespace ListarWP\Plugin\Libraries\Payment;
use ListarWP\Plugin\Libraries\Cart;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Models\Booking_Model;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Exception;
use HttpException;

class Payment_PayPal extends Payment_Abstract {

    /**
     * @var SandboxEnvironment
     */
    private $_environment;

    /**
     * @var PayPalHttpClient
     */
    private $_client;

    /**
     * Payment_PayPal constructor.
     * @param Customer $customer
     * @param Cart $cart
     * @throws Exception
     */
    public function __construct(Customer $customer = NULL, Cart $cart = NULL)
    {
        parent::__construct($customer, $cart);
        
        $this->title = __('PayPal CheckOut');

        // Default option from DB setting
        $client_id = get_option('listar_paypal_client_id');
        $client_secret = get_option('listar_paypal_client_secret');
        $env = get_option('listar_paypal_env');
        
        // Get from defined if can't found
        if(!$client_id) {
            if(!defined('LISTAR_PAYPAL_CLIENT_ID')) {
                throw new Exception(__('Please define PayPal clientId key'));
            }

            $client_id = LISTAR_PAYPAL_CLIENT_ID;
        }

        if(!$client_secret) {
            if(!defined('LISTAR_PAYPAL_CLIENT_SECRET')) {
                throw new Exception(__('Please define PayPal secret key'));
            }

            $client_secret = LISTAR_PAYPAL_CLIENT_SECRET;
        }

        // Build object
        if($env === 'sandbox') {
            $this->_environment = new SandboxEnvironment($client_id, $client_secret);
        } else if($env == 'live') {
            $this->_environment = new ProductionEnvironment($client_id, $client_secret);
        } else {
            throw new Exception(__('Please define PayPal Env'));
        }

        $this->_client = new PayPalHttpClient($this->_environment);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => $this->order_id,
                "amount" => [
                    "value" => $this->cart->total(),
                    "currency_code" => $this->currency
                ]
            ]],
            "application_context" => [
                "cancel_url" => $this->cancel_url,
                "return_url" => $this->success_url
            ]
        ];

        try {
            // Call API with your client and get a response for your call
            $response = $this->_client->execute($request);
            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            $this->result = $response->result;

            if($this->result && $this->result->status === 'CREATED') {
                $this->set_transaction_id($this->result->id);
            }
        } catch (HttpException $e) {
            throw new Exception( $e->getMessage());
        }
    }

    /**
     * Complete Payment
     * @param Booking_Model $order
     * @param string $token
     * @throws Exception
     */
    public function complete($order = NULL, $token = '')
    {
        try {
            // Call API with your client and get a response for your call
            $response = $this->_client->execute(new OrdersGetRequest($token));
            // Check & sync status with db
            if($response->result && ($response->result->status === 'APPROVED' || $response->result->status == 'CREATED')) {
                // On completed status
                parent::on_completed($order->get_id(), [
                    '_payment_meta' => json_encode((array)$response),
                    '_paid_date' => current_time('mysql')
                ]);
            }
        } catch (HttpException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Complete Payment
     * @param Booking_Model $order
     * @param string $token
     * @throws Exception
     */
    public function cancel($order = NULL, $token = '')
    {
        try {
            // Call API with your client and get a response for your call
            $response = $this->_client->execute(new OrdersGetRequest($token));

            // Check & sync status with db
            if($response->result && $response->result->status !== 'APPROVED') {
                // Set medata data callback
                update_post_meta($order->get_id(), '_payment_meta', json_encode($response));
                // Update status order
                wp_update_post([
                    'ID' => $order->get_id(),
                    'post_status' => 'canceled'
                ]);
            }
        } catch (HttpException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function get_title()
    {
        return $this->title;
    }
}
