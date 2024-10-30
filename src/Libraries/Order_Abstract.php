<?php
namespace ListarWP\Plugin\Libraries;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Libraries\Payment\Payment_Default;
use ListarWP\Plugin\Libraries\Payment\Payment_PayPal;
use ListarWP\Plugin\Libraries\Payment\Payment_Stripe;
use ListarWP\Plugin\Libraries\Payment\Payment_Bank;
use ListarWP\Plugin\Libraries\Payment\Payment_Cash;
use Exception;


abstract class Order_Abstract {

    protected $post_type = '';

    /**
     * Order ID
     * - Store in wp_post > ID
     * - It's auto increment order id
     * @var int
     */
    public $order_id = 0;

    /**
     * Default order status
     * @var string
     */
    public $status = 'pending';

    /**
     * Currency
     * @var string
     */
    public $currency = 'USD';

    /**
     * Payment method
     * @var string
     */
    public $payment_method = 'cash';

    /**
     *
     * create from where
     * @var string
     */
    public $created_via = 'admin';

    /**
     * Order title
     * @var string
     */
    public $title = '';

    /**
     * Customer
     * @var Customer|null
     */
    public $customer = NULL;

    /**
     * @var Cart|null
     */
    public $cart = NULL;

    /**
     * @var Payment_PayPal|Payment_Stripe
     */
    public $payment = NULL;

    /**
     * Create order & payment 
     * @throws Exception
     */
    public abstract function create_order();

     /**
     * Create order & payment 
     * @throws Exception
     */
    public abstract function update_order();

    /**
     * Order_Abstract constructor.
     * @param Cart $cart
     * @param Customer $customer
     */
    public function __construct(Cart $cart = NULL, Customer $customer = NULL)
    {
        $this->cart = $cart;
        $this->customer = $customer;

        // Set currency
        $currency = Setting_Model::get_option('unit_price');
        if($currency) {
            $this->currency = $currency;
        }
    }

    /**
     * setup props
     * @param array $data
     * @return self
     */
    public function initialize($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Set cart
     * @param Cart|NULL $cart
     */
    public function set_cart(Cart $cart = NULL)
    {
        $this->cart = $cart;
    }

    /**
     * Set currency
     * @param string $currency
     */
    public function set_currency($currency = '')
    {
        $this->currency = $currency;
    }

    /**
     * Set customer
     * @param Customer|NULL $customer
     */
    public function set_customer(Customer $customer = NULL)
    {
        $this->customer = $customer;
    }

    /**
     * Set created via
     * @param string $created_via
     */
    public function set_created_via($created_via = '')
    {
        $this->created_via = $created_via;
    }

    /**
     * Customize order title
     * @param string $title
     */
    public function set_title($title = '')
    {
        $this->title = $title;
    }

    /**
     * Set booking status
     * @param string $status
     */
    public function set_status($status = '')
    {
        $this->status = $status;
    }

    /**
     * Set payment method
     * @param string $payment_method
     * @throws Exception
     */
    public function set_payment_method($payment_method = '')
    {
        $this->payment_method = $payment_method;

        if(!Setting_Model::payment_use()) {
            $this->payment_method = 'default';
        }

        switch ($this->payment_method) {
            case 'paypal':
                $this->payment = new Payment_PayPal($this->customer, $this->cart);
                break;
            case 'stripe':
                $this->payment = new Payment_Stripe($this->customer, $this->cart);
                break;
            case 'cash':
                $this->payment = new Payment_Cash($this->customer, $this->cart);
                break;
            case 'bank':
                $this->payment = new Payment_Bank($this->customer, $this->cart);
                break;
            default:
                $this->payment = new Payment_Default($this->customer, $this->cart);
                break;
        }
    }

    /**
     * Billing & Payment
     *
     * Exception
     */
    public function create_billing_payment() {
        if(!($this->customer instanceof Customer)) {
            throw new Exception(__('Undefine customer', 'listar'));
        }

        // Create order meta
        $order_metadata = [
            '_customer_user' => $this->customer->get_id(),
            '_order_currency' => $this->currency,
            '_order_shipping_tax' => 0,
            '_txn_id' => '', // Transaction ID (PayPal, Stripe or 3rd party)
            '_prices_include_tax' => 'no',
            '_billing_address_index' => $this->customer->billing_address_index(),
            '_shipping_address_index' => '',
            '_cart_discount' => 0,
            '_cart_discount_tax' => 0,
            '_order_tax' => 0,
            '_order_total' => ($this->cart instanceof Cart) ? $this->cart->total() : 0,
            '_order_shipping' => 0,
            '_order_key' => $this->generate_order_key(), // Order ID (Local System)
            '_created_via' => $this->created_via,
            '_paid_date' => '',
            '_billing_first_name' => $this->customer->first_name,
            '_billing_last_name' => $this->customer->last_name,
            '_billing_company' => $this->customer->company,
            '_billing_address_1' => $this->customer->address,
            '_billing_city' => $this->customer->city,
            '_billing_country' => $this->customer->country,
            '_billing_email' => $this->customer->email,
            '_billing_phone' => $this->customer->phone
        ];

        // Create Payment (optional)
        if(Setting_Model::payment_use() && $this->payment) {
            $this->payment->set_order_id($this->order_id);
            $this->payment->set_currency($this->currency);
            $this->payment->create();
            $order_metadata['_txn_id'] = $this->payment->transaction_id;
            $order_metadata['_payment_method'] = $this->payment_method;
            $order_metadata['_payment_method_title'] = $this->payment->get_title();
        }
        
        // Final insert meta data
        $this->insert_meta_data($order_metadata, $this->order_id);
    }

    /**
     * Set meta data
     * @param array $metadata
     * @param int $order_id
     */
    public function insert_meta_data($metadata = [], $order_id = 0)
    {
        foreach($metadata as $key => $value) {
            update_post_meta($order_id, $key, $value);
        }
    }

    /**
     * Generate order id
     * @return string
     */
    public function generate_order_key()
    {
        return wp_generate_password( 13, false );
    }

    /**
     * Change booking status
     * @param int $order_id
     * @param string $new_status
     * @throws Exception
     */
    public function change_status($order_id = 0, $new_status = '')
    {
        global $wpdb;
        $query = "UPDATE ".$wpdb->prefix.'posts'." SET post_status = '$new_status' WHERE ID = {$order_id} ";
        $wpdb->query( $query);
        return TRUE;
    }
}
