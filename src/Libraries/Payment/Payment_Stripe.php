<?php
namespace ListarWP\Plugin\Libraries\Payment;
use ListarWP\Plugin\Libraries\Cart;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Models\Booking_Model;
use Exception;
use HttpException;

class Payment_Stripe extends Payment_Abstract {
    /**
     * @var \Stripe\StripeClient
     */
    private $_client;

    /**
     * API Key
     * @var string
     */
    private $_api_key = '';

    /**
     * Payment_PayPal constructor.
     * @param Customer $customer
     * @param Cart $cart
     * @throws Exception
     */
    public function __construct(Customer $customer = NULL, Cart $cart = NULL)
    {
        parent::__construct($customer, $cart);

        $this->title = __('Stripe CheckOut');

        $this->_api_key = get_option('listar_stripe_api_key');

        if(!$this->_api_key) {
            if (!defined('LISTAR_STRIPE_API_KEY')) {
                throw new Exception(__('Please define Stripe API key'));
            }

            $this->_api_key = LISTAR_STRIPE_API_KEY;
        }
    }

    /**
     *
     * @inheritDoc
     */
    public function create()
    {
        try {
            \Stripe\Stripe::setApiKey($this->_api_key);
            $base_url = get_site_url();

            if($this->cart->is_empty()) {
                throw new Exception(__('The booking content is empty. Please select items for booking and submit again.'));
            }

            // Build line items for send to Stripe format
            $line_items = [];
            foreach($this->cart->contents() as $item) {
                // Convert product price to cent
                // If we use int > it's error. Ex: 79$ will be 0.79$
                $price = round($item['total']*100, 2);

                $line_items[] = [
                    'price_data' => [
                        'unit_amount' => $price,
                        'currency' => strtolower($this->currency), // Three-letter ISO currency code in lowercase.
                        'product_data' => [
                            'name' => $item['name'],
                        ]
                    ],
                    'quantity' => $item['qty'],
                ];
            }

            // Stripe Checkout
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'client_reference_id' => $this->order_id,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $this->success_url,
                'cancel_url' => $this->cancel_url,
            ]);

            // Set response when success
            if($session) {
                $this->set_transaction_id($session->id);
            }

            $this->result = $session;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Complete payment
     * @param Booking_Model $order
     * @param string $token
     * @return mixed|void
     * @throws Exception
     */
    public function complete($order = NULL, $token = '')
    {
        try {
            $this->_client = new \Stripe\StripeClient($this->_api_key);
            $response = $this->_client->checkout->sessions->retrieve($token, []);

            if($response && $response->payment_status == 'paid') {
                // On completed status
                parent::on_completed($order->get_id(), [
                    '_payment_meta' => json_encode((array)$response),
                    '_txn_id' => $response->payment_intent,
                    '_paid_date' => current_time('mysql')
                ]);

                $this->set_transaction_id($response->payment_intent);
            }
        } catch (HttpException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Complete payment
     * @param Booking_Model $order
     * @param string $token
     * @return mixed|void
     * @throws Exception
     */
    public function cancel($order = NULL, $token = '')
    {
        try {
            $this->_client = new \Stripe\StripeClient($this->_api_key);
            $response = $this->_client->checkout->sessions->retrieve($token, []);
            if($response && $response->payment_status == 'unpaid') {
                // Set medata data callback
                update_post_meta($order->get_id(), '_payment_meta', json_encode((array)$response));
                update_post_meta($order->get_id(), '_txn_id', $response->payment_intent);
                // Update status order
                wp_update_post([
                    'ID' => $order->get_id(),
                    'post_status' => 'canceled'
                ]);
                $this->set_transaction_id($response->payment_intent);
            }
        } catch (HttpException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Check with token flow
     * - Verify token with Stripe
     * - Get token and process payment
     * @throws Exception
     */
    public function token()
    {
        try {
            $intent = \Stripe\PaymentIntent::create([
                'payment_method' => $this->transaction_id,
                'amount' => $this->cart->total(),
                'currency' => $this->currency,
                'payment_method_types' => ['card'],
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            if (isset($this->payment_intent_id)) {
                $intent = \Stripe\PaymentIntent::retrieve(
                    $this->payment_intent_id
                );
                $intent->confirm();
            }

            // Set response
            $this->result = $intent;

            if ($intent->status == 'requires_action' && $intent->next_action->type == 'use_stripe_sdk') {
                $this->result = [
                    'requires_action' => true,
                    'payment_intent_client_secret' => $intent->client_secret
                ];
            } else if ($intent->status == 'succeeded') {
                update_post_meta($this->order_id, '_payment_meta', json_encode((array)$intent));
                wp_update_post([
                    'ID' =>$this->order_id,
                    'post_status' => 'completed'
                ]);
            } else {
                update_post_meta($this->order_id, '_payment_meta', json_encode((array)$intent));
                wp_update_post([
                    'ID' => $this->order_id,
                    'post_status' => 'failed'
                ]);
                throw new Exception(__('Invalid PaymentIntent status'));
            }
        } catch (HttpException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
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
