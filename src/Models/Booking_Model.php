<?php
namespace ListarWP\Plugin\Models;
use ListarWP\Plugin\Listar;
use WP_Query;
use Exception;

class Booking_Model {

    /**
     * Set data include meta data
     * @var array
     */
    public $data = [];

    public function __construct()
    {
    }

    /**
     * return post type
     * @return string
     */
    public static function post_type()
    {
        return Listar::$post_type.'_booking';
    }


    /**
     * Get item model
     * @param int $booking_id
     * @return Booking_Model
     * @throws Exception
     */
    public function item($booking_id = 0)
    {
        $booking_id = absint($booking_id);

        if ( $booking_id <= 0 ) {
            throw new Exception(__( 'Invalid booking ID.', 'listar'));
        }

        $post = get_post( (int) $booking_id);
        if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== self::post_type()) {
            throw new Exception(__( 'Invalid booking data.', 'listar'));
        }

        $this->data = (array)$post;


        $this->assign_meta_data($post->ID);

        return $this;
    }

    /**
     * Get item model
     * @param string $meta_key
     * @param string $meta_value
     * @return Booking_Model
     * @throws Exception
     */
    public function item_condition($meta_key = '', $meta_value = '', $status = 'publish')
    {
        if ( $meta_key && $meta_value) {
            $posts = get_posts( [
                'meta_query' => [
                    [
                        'key' => $meta_key,
                        'value' => $meta_value
                    ]
                ],
                'post_type' => self::post_type(),
                'post_status' => $status,
                'posts_per_page' => '1'
            ]);

            if ($posts && !is_wp_error($posts)) {
                $post = $posts[0];
            } else {
                throw new Exception(__( 'Data booking was not found.', 'listar'));
            }
        } else {
            throw new Exception(__( 'Invalid booking condition.', 'listar'));
        }

        if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== self::post_type()) {
            throw new Exception(__( 'Invalid booking data.', 'listar'));
        }

        $this->data = (array)$post;

        $this->assign_meta_data($post->ID);

        return $this;
    }

    /**
     * Assign meta data
     * @param int $post_id
     * @param array $data
     */
    public function assign_meta_data($post_id = 0, $data = [])
    {
        // Booking meta data
        $meta_data = get_post_meta($post_id);
        if(!empty($meta_data)) {
            foreach ($meta_data as $key => $value) {
                $this->data[$key] = listar_get_single_value($meta_data[$key]);
            }
        }

        if(!empty($data)) {
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Get item list
     * > Item for booking list
     * @return array
     */
    public function get_item_list()
    {
        return [
            'ID' => $this->data['ID'],
            'title' => $this->data['post_title'],
            'booking_id' => $this->data['ID'],
            'status_name' => $this->get_status_name(),
            'first_name' => $this->get_value('_billing_first_name'),
            'last_name' => $this->get_value('_billing_last_name'),
            'status_color' => $this->get_status_color(),
            'date' => $this->data['post_date'],
        ];
    }

    /**
     * Get booking view
     * > Item for booking view
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_item_view($id = 0)
    {
        $item = $this->item($id);

        $data = [
            'title' => $item->get_title(),
            'booking_id' => $item->get_booking_id(),
            'payment' => $this->get_payment_method() !== '' ? __('Yes') : __('No'),
            'status_name' => $this->get_status_name(),
            'status_color' => $this->get_status_color(),
            'created_on' => $item->get_created_date(),
            'paid_date' => $item->get_paid_date(),
            'payment_name' => $item->get_payment_method_name(),
            'payment_method' => $item->get_payment_method(),
            'create_via' => $item->get_value('_created_via'),
            'txn_id' => $item->get_txn_id(),
            'total' => $item->get_total(),
            'currency' => $item->get_currency(),
            'total_display' => Setting_Model::currency_format($item->get_total(), $item->get_currency()),
            'customer_user' => $item->get_customer_user(), // for send notification
            'author' => $item->get_author(), // for send notification (mobile push token)
            'author_email' => $item->get_author_email(), // for send notification (email)
        ];

        $data = array_merge($data, $item->get_billing());
        $data['resources'] = $item->get_resources();

        // Action status
        $data['allow_cancel'] = $this->get_status() == 'pending';
        $data['allow_payment'] = $this->get_status() == 'pending' && $this->get_value('_txn_id') != '';

        /**
         * Accept request booking status button
         * @since 1.0.23
         * - User login is admin
         * - Not payment support
         */
        $data['allow_accept'] = user_can( $item->get_author(), "manage_options" )
            && $this->get_status() == 'pending' && !$this->get_value('_txn_id');

        return $data;
    }

    /**
     * Get id
     * @return int
     */
    public function get_id()
    {
        return (int) $this->get_value('ID');
    }

    /**
     * Get booking id
     * @return mixed|string
     */
    public function get_booking_id()
    {
        return $this->get_value('ID');
    }

    /**
     * Get transaction ID
     * @return mixed|string
     */
    public function get_txn_id()
    {
        return $this->get_value('_txn_id');
    }

    /**
     * Booking title
     * @return string
     */
    public function get_title()
    {
        return $this->get_value('post_title');
    }

    /**
     * Booking status (row data)
     * @return string
     */
    public function get_status()
    {
        return $this->get_value('post_status');
    }

    /**
     * Booking status
     * @return string
     */
    public function get_status_name()
    {
        $status_list = listar_booking_status();
        return isset($status_list[$this->get_status()]) ? $status_list[$this->get_status()]['title'] : __('Undefined', 'listar');
    }

    /**
     * Booking status
     * @return string
     */
    public function get_status_color()
    {
        $status_list = listar_booking_status();
        return isset($status_list[$this->get_status()]) ? $status_list[$this->get_status()]['color'] : '#e5634d';
    }

    /**
     * Get booking style list
     * @return array
     */
    public static function get_booking_style_list()
    {
        return [
            ['value' => 'standard', 'title' => __('Standard')],
            ['value' => 'slot', 'title' => __('Slot')],
            ['value' => 'hourly', 'title' => __('Hourly')],
            ['value' => 'daily', 'title' => __('Daily')],
            //['value' => 'table', 'title' => __('table')],
        ];
    }

    /**
     * Booking status
     * @return string
     */
    public function get_created_date()
    {
        $date = $this->get_value('post_date');
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        return date($date_format.' '.$time_format, strtotime($date));
    }

    /**
     * Booking status
     * @return string
     */
    public function get_currency()
    {
        return $this->get_value('_order_currency');
    }

    /**
     * Get total
     * @return mixed|string
     */
    public function get_total()
    {
        return absint($this->get_value('_order_total'));
    }

    /**
     * Get billing address
     * @return mixed|string
     */
    public function get_billing_address_index()
    {
        return $this->get_value('_billing_address_index');
    }

    /**
     * Get payment method
     * @return mixed|string
     */
    public function get_payment_method() {
        return $this->get_value('_payment_method');
    }

    /**
     * Get paid date
     * @return mixed|string
     */
    public function get_paid_date() {
        $date = $this->get_value('_paid_date');
        if($date) {
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            return date($date_format . ' ' . $time_format, strtotime($date));
        }
        return '';
    }

    /**
     * Get payment method name
     * @return mixed|string
     */
    public function get_payment_method_name() {
        return $this->get_value('_payment_method_title');
    }

    /**
     * Get billing data
     * @return array
     */
    public function get_billing()
    {
        return [
            'billing_first_name' => $this->get_value('_billing_first_name'),
            'billing_last_name' => $this->get_value('_billing_last_name'),
            'billing_company' => $this->get_value('_billing_company'),
            'billing_address_1' => $this->get_value('_billing_address_1'),
            'billing_city' => $this->get_value('_billing_city'),
            'billing_country' => $this->get_value('_billing_country'),
            'billing_email' => $this->get_value('_billing_email'),
            'billing_phone' => $this->get_value('_billing_phone'),
            'billing_address_index' => $this->get_value('_billing_address_index'),
        ];
    }

    /**
     * Get customer user id
     * @return mixed|string
     */
    public function get_customer_user()
    {
        return $this->get_value('_customer_user');
    }

    /**
     * Get author ID > who made the listing
     * @return mixed|string
     * @since 1.0.21
     */
    public function get_author()
    {
        return $this->get_value('_author');
    }

    /**
     * Get author email for send email notification
     * @return mixed|string
     * @since 1.0.30
     */
    public function get_author_email()
    {
        return $this->get_value('_author_email');
    }

    /**
     * Get customer email
     * @return mixed|string
     * @since 1.0.30
     */
    public function get_customer_email()
    {
        return $this->get_value('_billing_email');
    }

    /**
     * Get booking type
     * @return mixed|string
     */
    public function get_booking_type()
    {
        return $this->get_value('_booking_type');
    }

    /**
     * Get lis resource data
     * @return array
     */
    public function get_resources()
    {
        $booking_item_model = new Booking_Item_Model();
        $items = $booking_item_model->get_booking_item($this->get_value('ID'));
        $result = [];

        if(!empty($items)) {
            foreach ($items as $item) {
                $result[] = [
                    'id' => $item['booking_item_id'],
                    'name' => $item['booking_item_name'],
                    'resource_id' => $item['_resource_id'],
                    'qty' => absint($item['_qty']),
                    'total' => absint($item['_line_total']),
                    'total_display' => Setting_Model::currency_format($item['_line_total'], $this->get_currency())
                ];
            }
        }

        return $result;
    }

    /**
     * Get single resource
     * @return array|mixed
     */
    public function get_single_resource() {
        $resources = $this->get_resources();
        return isset($resources[0]) ? $resources[0] : [];
    }

    /**
     * Get memo value
     * @return mixed|string
     */
    public function get_memo() {
        return $this->get_value('_memo');
    }

    /**
     * Get value
     * @param string $key
     * @return mixed|string
     */
    public function get_value($key = '')
    {
        return isset($this->data[$key]) ? $this->data[$key] : '';
    }

    /**
     * Check user has booked
     *
     * @param integer $user_id
     * @param integer $listing_id
     * @return boolean
     */
    public static function has_booked_data($user_id = 0, $listing_id = 0)
    {
        /**
         * Check table wp_postmeta
         * - check listing id 
         * - check meta > _customer_user = user_id
         */
        $query = new WP_Query([
            'post_type' => self::post_type(),
            'post_status' => 'completed',
            'post_author' => absint($user_id),        
            'post_parent' => absint($listing_id)
        ]);
        return $query->found_posts > 0 ? true : false; 
    }
}
