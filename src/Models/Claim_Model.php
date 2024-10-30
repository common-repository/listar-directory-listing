<?php
namespace ListarWP\Plugin\Models;
use ListarWP\Plugin\Listar;
use WP_Query;
use Exception;

class Claim_Model {

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
        return Listar::$post_type.'_claim';
    }


    /**
     * Get item model
     * @param int $post_id
     * @return Claim_Model
     * @throws Exception
     */
    public function item($post_id = 0)
    {
        $post_id = absint($post_id);

        if ( $post_id <= 0 ) {
            throw new Exception(__( 'Invalid ID.', 'listar'));
        }

        $post = get_post( (int) $post_id);
        if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== self::post_type()) {
            throw new Exception(__( 'Data could be not found.', 'listar'));
        }

        $this->data = (array)$post;


        $this->assign_meta_data($post->ID);

        return $this;
    }

    /**
     * Get item model
     * @param string $meta_key
     * @param string $meta_value
     * @return Claim_Model
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
                throw new Exception(__( 'Data could be not found.', 'listar'));
            }
        } else {
            throw new Exception(__( 'Invalid condition.', 'listar'));
        }

        if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== self::post_type()) {
            throw new Exception(__( 'Data could be not found.', 'listar'));
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
        // claim meta data
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
     * > Item for claim list
     * @return array
     */
    public function get_item_list()
    {   
        $order_total = get_post_meta($this->data['ID'], '_order_total', true);
        $order_currency = get_post_meta($this->data['ID'], '_order_currency', true);
        if($order_total && $order_currency) {
            $currency_format = Setting_Model::currency_format($order_total, $order_currency);
        } else {
            $currency_format = '';
        }

        return [
            'ID' => $this->data['ID'],
            'title' => $this->data['post_title'],
            'address' => get_post_meta($this->data['ID'], '_listing_address', true),
            'currency_format' => $currency_format,
            'due_date' => date('Y-m-d H:s'),
            'claim_id' => $this->data['ID'],
            'status_name' => $this->get_status_name(),
            'first_name' => $this->get_value('_billing_first_name'),
            'last_name' => $this->get_value('_billing_last_name'),
            'status_color' => $this->get_status_color(),
            'date' => $this->data['post_date'],
            //'total' => $this->get_total(),
            //'currency' => $this->get_currency(),            
            //'total_display' => Setting_Model::currency_format($this->get_total(), $this->get_currency()),
            //'due_date' => '', develop later
            'total_display' => $this->get_claim_charge_fee_disc()
        ];
    }

    /**
     * Get data view
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get_item_view($id = 0)
    {
        $item = $this->item($id);

        if($this->get_status() == 'completed') {
            $total_display = Setting_Model::currency_format($item->get_total(), $item->get_currency());
        } else {
            $total_display = $this->get_claim_charge_fee_disc();
        }
        $data = [
            'title' => $item->get_title(),
            'post_title' => $item->get_title(),
            'claim_id' => $item->get_claim_id(),
            'claimed' => $item->has_claimed(),
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
            'total_display' => $total_display,
            'customer_user' => $item->get_customer_user(), // for send notification
            'author' => $item->get_author(), // for send notification (mobile push token)
            'author_email' => $item->get_author_email(), // for send notification (email),
            'claim_method_charge' => $this->get_claim_method_charge(),
            'claim_price' => $this->get_claim_charge_fee(),
            'claim_unit_price' => $this->get_claim_charge_unit()
        ];

        $data = array_merge($data, $item->get_billing());

        // Action status for user   
        $data['allow_cancel'] = $this->get_status() == 'pending' || $this->get_status() == 'publish'; 
        $data['allow_payment'] = $this->get_status() == 'publish';

        // Action status for admin
        $data['allow_accept'] = user_can( $item->get_author(), "manage_options" ) && $this->get_status() == 'pending';

        return $data;
    }

    /**
     * Get claim id
     * @return mixed|string
     */
    public function get_claim_id()
    {
        return $this->get_value('ID');
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
     * Get resource ID from claim request
     *
     * @return int
     */
    public function get_resource_id()
    {
        return $this->get_value('ID');
    }

    /**
     * Get listing ID
     * @return int
     */
    public function get_listing_id()
    {
        return $this->get_value('post_parent');
    }

    /**
     * Has claim
     * @return boolean
     */
    public function has_claimed()
    {
        return (int) $this->get_value('claim_use') === 1;
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
     * claim title
     * @return string
     */
    public function get_title()
    {
        return $this->get_value('post_title');
    }

    /**
     * claim status (row data)
     * @return string
     */
    public function get_status()
    {
        return $this->get_value('post_status');
    }

    /**
     * claim status
     * @return string
     */
    public function get_status_name()
    {
        $status_list = listar_claim_status();
        return isset($status_list[$this->get_status()]) ? $status_list[$this->get_status()]['title'] : __('Undefined', 'listar');
    }

    /**
     * claim status
     * @return string
     */
    public function get_status_color()
    {
        $status_list = listar_claim_status();
        return isset($status_list[$this->get_status()]) ? $status_list[$this->get_status()]['color'] : '#e5634d';
    }

    /**
     * claim status
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
     * claim status
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
     * Get claim type
     * @return mixed|string
     */
    public function get_claim_type()
    {
        return $this->get_value('_claim_type');
    }

    /**
     * Get memo value
     * @return mixed|string
     */
    public function get_memo() {
        return $this->get_value('_memo');
    }

    /**
     * Get claim method charge
     * @return string
     */            
    public function get_claim_method_charge() {
        return $this->get_value('claim_method_charge');
    }

     /**
     * Get claim method charge name
     * @return string
     */            
    public function get_claim_method_charge_name() {
        $methods = listar_claim_method_charges();
        $claim_method_charge = $this->get_value('claim_method_charge');
        return isset($methods[$claim_method_charge]) ? $methods[$claim_method_charge]['title'] : __('Undefined');
    }

    /**
     * Get claim fee
     *
     * @return int|float|double
     */
    public function get_claim_charge_fee() {
        return  $this->get_value('claim_price');
    }

    /**
     * Get claim fee for display include init of price
     *
     * @return int|float|double
     */
    public function get_claim_charge_fee_disc() {
        $fee = $this->get_claim_charge_fee();
        return $fee > 0 ? Setting_Model::currency_format($fee, $this->get_claim_charge_unit()) : '';
    }

    /**
     * Get claim unit price
     *
     * @return int|float|double
     */
    public function get_claim_charge_unit() {
        return  $this->get_value('claim_unit_price');
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
     * Set prop image
     *
     * @param int $post_id
     * @author Paul <paul.passionui@gmail.com>
     * @return array
     * @version 1.0.31
     */
    public static function get_image($post_id) {

        $image_id = get_post_thumbnail_id($post_id);
        $image_url = get_the_post_thumbnail_url($post_id);

        if((int) $image_id > 0 && $image_url) {
            $image = [
                'id' => get_post_thumbnail_id($post_id),
                'full' => ['url' => $image_url],
                'medium' => ['url' => get_the_post_thumbnail_url($post_id, 'medium')],
                'thumb' => ['url' => get_the_post_thumbnail_url($post_id, 'thumb')],
            ];
        } else {
            $image = Setting_Model::default_image();
        }

        return $image;
    }

    /**
     * Check user has claimed
     *
     * @param integer $user_id
     * @param integer $listing_id
     * @return boolean
     */
    public static function has_claimed_data($user_id = 0, $listing_id = 0)
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
