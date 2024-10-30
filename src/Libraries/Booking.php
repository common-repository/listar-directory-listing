<?php
namespace ListarWP\Plugin\Libraries;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Booking_Model;
use ListarWP\Plugin\Models\Booking_Item_Model;
use ListarWP\Plugin\Libraries\Product;
use ListarWP\Plugin\Libraries\Booking\Booking_Abstract;
use ListarWP\Plugin\Libraries\Booking\Booking_Standard;
use ListarWP\Plugin\Libraries\Booking\Booking_Slot;
use ListarWP\Plugin\Libraries\Booking\Booking_Table;
use ListarWP\Plugin\Libraries\Booking\Booking_Daily;
use ListarWP\Plugin\Libraries\Booking\Booking_Hourly;
use ListarWP\Plugin\Libraries\Payment\Payment_PayPal;
use ListarWP\Plugin\Libraries\Payment\Payment_Stripe;
use ListarWP\Plugin\Libraries\Notify;

use Exception;
use Stripe\Payout;

/**
 * Class Booking
 * > For run booking business
 */
class Booking extends Order_Abstract {

    /**
     * Booking type list
     * @var array
     */
    static $booking_style_list = ['standard', 'slot', 'table', 'hourly', 'daily'];

    /**
     * booking type
     * - standard, slot, table, hourly, daily
     * @var string
     */
    public $booking_type = '';

    /**
     * @var Booking_Abstract
     */
    public $booking_style = NULL;

    /**
     * @var Product
     */
    public $resource = NULL;

    /**
     * resource ID
     * @var null
     */
    protected $resource_id = NULL;

    /**
     * Booking constructor.
     * @param string $booking_style
     * @param int $resource_id
     * @throws Exception
     */
    public function __construct($booking_style = '', $resource_id = 0)
    {
        parent::__construct();
        $this->post_type = Listar::$post_type.'_booking';

        if($booking_style) {
            $this->set_booking_style($booking_style);
        }

        if($resource_id) {
            $this->set_resource($resource_id);
        }

        return $this;
    }

    /**
     * Set payment methid
     *
     * @param string $payment_method
     * @throws Exception
     */
    public function set_payment_method($payment_method = '')
    {   
        $base_url = get_site_url();
        parent::set_payment_method($payment_method);
        
        if($this->payment instanceof Payment_PayPal) {
            $this->payment->set_success_url($base_url."/index.php/wp-json/listar/v1/booking/return");
            $this->payment->set_cancel_url($base_url."/index.php/wp-json/listar/v1/booking/cancel");
        } else if($this->payment instanceof Payment_Stripe) {
            $this->payment->set_success_url($base_url."/index.php/wp-json/listar/v1/booking/return/?token={CHECKOUT_SESSION_ID}");
            $this->payment->set_cancel_url($base_url."/index.php/wp-json/listar/v1/booking/cancel/?token={CHECKOUT_SESSION_ID}");
        }
    }

    /**
     * Set resource
     * @param int $resource_id
     * @throws Exception
     */
    public function set_resource($resource_id = 0)
    {
        $post = Listar::valid_post($resource_id);
        if($post) {
            $price = get_post_meta($post->ID, 'booking_price', TRUE);
            $disable = get_post_meta($post->ID, 'booking_disable', TRUE);
           
            if(!$price) {
                throw new Exception(__('The price for booking the resource was not found. #ID='. $resource_id));
            }

            if($disable) {
                throw new Exception(__('The resource for booking is disabled'));
            }

            // Get author email
            $author = get_user_by('id', $post->post_author);

            $this->resource = new Product([
                'id' => $post->ID,
                'name' => $post->post_title,
                'price' => $price,
                'author' => $post->post_author,
                'author_email' => !empty($author) ? $author->user_email : '',
            ]);
        } else {
            throw new Exception(__('The resource data for booking was not found. #ID='. $resource_id));
        }
    }

    /**
     * Set booking style
     * @param string $booking_type
     * @return Booking_Abstract|Booking_Daily|Booking_Hourly|Booking_Slot|Booking_Standard|Booking_Table
     * @throws Exception
     */
    public function set_booking_style($booking_type = '')
    {
        $this->booking_type = $booking_type;
        if(!in_array($this->booking_type, self::$booking_style_list)) {
            throw new Exception(__('Non support booking style '.$this->booking_type));
        }
        switch ($this->booking_type) {
            case 'slot':
                $this->booking_style = new Booking_Slot();
                break;
            case 'table':
                $this->booking_style = new Booking_Table();
                break;
            case 'daily':
                $this->booking_style = new Booking_Daily();
                break;
            case 'hourly':
                $this->booking_style = new Booking_Hourly();
                break;
            default:
                $this->booking_style = new Booking_Standard();
                break;
        }

        return $this->booking_style;
    }

    /**
     * Place order
     * @throws Exception
     */
    public function create_order()
    {
        global $wpdb;

        try {
            if(!$this->booking_style) {
                throw new Exception(__('Undefined booking style'));
            }

            $this->booking_style->validate();

            if(!$this->resource) {
                throw new Exception(__('Undefined booking resource'));
            }

            // Start transaction
            $wpdb->query('START TRANSACTION');

            // Customize booking title
            parent::set_title('Booking ' . sprintf("%04d", rand(0, 1000)));

            // Crate order
            $data = [
                'post_type' => $this->post_type,
                'post_title' => $this->title,
                'post_status' => $this->status,
                'post_parent' => $this->resource->get_id(), // link with real listing
            ];

            $this->order_id = wp_insert_post($data, TRUE);

            if(!$this->order_id) {
                throw new Exception(__('Create place order is failed.', 'listar'));
            }

            // Create billing & payment
            parent::create_billing_payment();

            // Create booking meta
            parent::insert_meta_data($this->booking_style->booking_meta_data(), $this->order_id);
            parent::insert_meta_data(['_booking_type' => $this->booking_type], $this->order_id);
            parent::insert_meta_data(['_author' => $this->resource->get_author()], $this->order_id);
            parent::insert_meta_data(['_author_email' => $this->resource->get_author_email()], $this->order_id);

            // Create booking items & metadata
            $items = $this->cart->contents();
            if (!empty($items)) {
                foreach ($items as $item) {
                    Booking_Item_Model::insert($this->order_id, $item['name'], [
                        '_resource_id' => $item['id'],
                        '_qty' => $item['qty'],
                        '_line_total' => $item['total'],
                        '_options' => $item['options'],
                    ]);

                    // Link with real listing
                    wp_update_post([
                        'ID' => $this->order_id,
                        'post_parent' => $item['id']
                    ]);
                }
            }
            $wpdb->query('COMMIT');

            // Booking data
            $booking_model = new Booking_Model();
            $data = $booking_model->get_item_view($this->order_id);

            /**
             * Reformat booking title
             * @since 1.0.21
             */
            if(Setting_Model::get_option('booking_title_format')) {
                $new_title = listar_pattern_replace($data, Setting_Model::get_option('booking_title_pattern'));
                wp_update_post([
                    'ID' => $this->order_id,
                    'post_title' => $new_title
                ]);
                $data['title'] = $new_title;
            }

            // Send notification
            Notify::notify_create_booking($data);

        } catch (Exception $e) {
            // roll back everything
            $wpdb->query('ROLLBACK');
            // Log error
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Update order
     * @param int $order_id
     * @throws Exception
     */
    public function update_order($order_id = 0)
    {
        try {
            $order = get_post( $this->order_id);
            // Set order id
            $this->order_id = $order_id;

            $this->create_billing_payment();

            // Reset booking item
            Booking_Item_Model::delete($this->order_id);

            // Create booking meta
            parent::insert_meta_data($this->booking_style->booking_meta_data(), $this->order_id);
            parent::insert_meta_data(['_booking_type' => $this->booking_type], $this->order_id);

            // Create booking items & metadata
            $items = $this->cart->contents();
            if (!empty($items)) {
                foreach ($items as $item) {
                    Booking_Item_Model::insert($this->order_id, $item['name'], [
                        '_resource_id' => $item['id'],
                        '_qty' => $item['qty'],
                        '_line_total' => $item['total'],
                        '_options' => $item['options'],
                    ]);
                }
            }

            // Update status order only change
            if($order->post_status != $this->status) {
                $this->change_status($this->order_id, $this->status);

                // Send notification
                $booking_model = new Booking_Model();
                $booking_data = $booking_model->get_item_view($order_id);

                // Send notification to input email and user made booking too
                $email_recipient = $booking_model->get_value('_billing_email');
                $user_data = get_user_by('id', $booking_model->get_customer_user());
                if($user_data) {
                   if($email_recipient != $user_data->user_email) {
                       $email_recipient .= ', '.$user_data->user_email;
                   }
                }

                // Set status
                $booking_data['from_status'] = $order->post_status;
                $booking_data['to_status'] = $this->status;

                Notify::notify_status_booking($booking_data, $email_recipient);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Accept payment by id
     * @param int $booking_id
     * @throws Exception
     * @since 1.0.23
     */
    public function accept($booking_id = 0)
    {
        try {
            $booking_model = new Booking_Model();
            $booking = $booking_model->item($booking_id);
            $booking_data = $booking_model->get_item_view($booking_id);

            if($booking->get_value('_txn_id') != '') {
                throw new Exception(__('The booking is having transaction #ID. Can not process the request', 'listar'));
            }

            if($booking->get_status() === 'pending') {
                // Update status order
                wp_update_post([
                    'ID' => $booking_id,
                    'post_status' => 'publish'
                ]);
                // Send notification
                $email = $booking_model->get_value('_billing_email');
                if($email) {
                    Notify::notify_complete_booking($email, $booking_data);
                }
            } else {
                throw new Exception(__('Can not process request. The status must be pending', 'listar'));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Cancel payment by booking id
     * @param int $booking_id
     * @throws Exception
     */
    public function cancel($booking_id = 0)
    {
        try {
            $booking_model = new Booking_Model();
            $booking = $booking_model->item($booking_id);
            $booking_data = $booking_model->get_item_view($booking_id);

            if($booking->get_status() === 'pending') {
                // Update status order
                wp_update_post([
                    'ID' => $booking_id,
                    'post_status' => 'canceled'
                ]);
                // Send notification
                Notify::notify_cancel_booking($booking_data);
            } else {
                throw new Exception(__('Undefined status for cancelling. The status must be pending'));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Complete payment
     * @param string $transaction_id
     * @throws Exception
     */
    public function complete_payment($transaction_id = '')
    {
        if(!Setting_Model::payment_use()) {
            throw new Exception('The payment feature has been disabled');
        }

        $booking_model = new Booking_Model();
        $order = $booking_model->item_condition('_txn_id', $transaction_id, 'pending');
        $this->set_payment_method($order->get_payment_method());
        $this->payment->order_id = $order->get_booking_id();
        $booking_data = $booking_model->get_item_view($order->get_booking_id());

        try {
            $this->payment->complete($order, $transaction_id);

            $email = $booking_model->get_value('_billing_email');
            if($email) {
                Notify::notify_complete_booking($email, $booking_data);
            }
        } catch (Exception $e) {
            Notify::notify_fail_booking($booking_data);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Cancel payment
     * @param string $transaction_id
     * @throws Exception
     */
    public function cancel_payment($transaction_id = '')
    {
        if(!Setting_Model::payment_use()) {
            throw new Exception('The payment feature has been disabled');
        }

        $booking_model = new Booking_Model();
        $order = $booking_model->item_condition('_txn_id', $transaction_id, 'pending');
        $this->set_payment_method($order->get_payment_method());
        $this->payment->order_id = $order->get_booking_id();
        $booking_data = $booking_model->get_item_view($order->get_booking_id());
        try {
            $this->payment->cancel($order, $transaction_id);
            Notify::notify_cancel_booking($booking_data);
        } catch (Exception $e) {
            Notify::notify_fail_booking($booking_data);
            throw new Exception($e->getMessage());
        }
    }
}
