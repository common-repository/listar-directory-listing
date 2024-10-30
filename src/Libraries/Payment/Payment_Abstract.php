<?php
namespace ListarWP\Plugin\Libraries\Payment;
use Exception;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Libraries\Cart;

abstract class Payment_Abstract {
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Get result response from 3rd
     * - PayPal
     * - Stripe
     * @var array
     */
    public $result = [];

    /**
     * transaction id
     * - PayPal, Stripe
     * @var string
     */
    public $transaction_id = '';

    /**
     * System order id
     * Wordpress
     * @var int
     */
    public $order_id;

    /**
     * Currency
     * @var string
     */
    public $currency = '';

    /**
     * Payment title
     * @var string
     */
    public $title = '';

    /**
     * Success URL
     *
     * @var string
     */
    public $success_url = '';

    /**
     * Cancel URL
     *
     * @var string
     */
    public $cancel_url = '';

    /**
     * Payment_Abstract constructor.
     * @param Customer $customer
     * @param Cart $cart
     */
    public function __construct(Customer $customer = NULL, Cart $cart = NULL)
    {
        $this->customer = $customer;
        $this->cart = $cart;
    }

    /**
     * call when create order internal system
     * @return mixed
     * @throws Exception
     */
    public abstract function create();

    /**
     * call when completed order
     * @return mixed
     * @throws Exception
     */
    public abstract function complete();

    /**
     * call when cancel order internal system
     * @return mixed
     * @throws Exception
     */
    public abstract function cancel();

    /**
     * Payment title
     * @return mixed
     */
    public abstract function get_title();

    /**
     * Get result after call remote service
     * @return array
     */
    public function get_response()
    {
        return $this->result;
    }

    /**
     * Set transaction id
     * @param string $id
     */
    public function set_transaction_id($id = '')
    {
        $this->transaction_id = $id;
    }

    /**
     * Set order id
     * @param int $id
     */
    public function set_order_id($id = 0)
    {
        $this->order_id = $id;
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
     * On completed action
     * @param int $booking_id
     * @param array $meta
     */
    public function on_completed($booking_id = 0, $meta = [])
    {
        wp_update_post([
            'ID' => $booking_id,
            'post_status' => 'completed'
        ]);

        if(!empty($meta)) {
            foreach($meta as $key => $value) {
                update_post_meta($booking_id, $key, $value);
            }
        }
    }

    /**
     * Set callback success url
     * - PayPal, Stripe
     * 
     * @param string $url
     */
    public function set_success_url($url = '')
    {
        $this->success_url = $url;
    }

    /**
     * Set callback cancel url
     * - PayPal, Stripe
     * 
     * @param string $url
     */
    public function set_cancel_url($url = '')
    {
        $this->cancel_url = $url;
    }
}
