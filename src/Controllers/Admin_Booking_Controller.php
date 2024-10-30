<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Booking_Model;
use ListarWP\Plugin\Libraries\Booking;
use ListarWP\Plugin\Libraries\Cart;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Libraries\Notice;
use WP_Post;
use Exception;
use WP_Query;

class Admin_Booking_Controller
{
    /**
     * Post type support
     * @var string
     */
    protected $post_type = '';

    public function __construct()
    {
        $this->post_type = Listar::$post_type.'_booking';

        // Load scripts via admin
        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);

        // Customize post metadata
        add_action('add_meta_boxes', [$this, 'add']);

        // Customize list post
        add_action('pre_get_posts', [$this, 'pre_get_posts']);
        add_filter('wp_count_posts', [$this, 'filter_list_table_counts']);
        add_action( 'current_screen', function ( $screen ) {
            if( $screen->id == 'edit-'.$this->post_type){
                add_filter( 'wp_count_posts', [$this, 'filter_list_table_counts'] );
            }
        });

        /**
         * Customize save_post
         */
        add_action('save_post_'.$this->post_type, [$this, 'save'], 10, 3); // Fires once a post has been saved
        add_action('pre_post_update', [$this, 'pre_post_update'], 10, 2);

        // Customize post status
        add_action('admin_footer-edit.php', [$this, 'admin_footer_edit']);
        add_action('admin_footer-post.php', [$this, 'admin_footer_post']);
        add_action('admin_footer-post-new.php', [$this, 'admin_footer_post_new']);

        /**
         * Customize display list
         * @since 1.0.11
         */
        add_filter( 'manage_'.$this->post_type.'_posts_columns', [$this, 'booking_columns_filter'] );
        add_action( 'manage_'.$this->post_type.'_posts_custom_column', [$this, 'booking_columns_action'], 10, 2);
    }

    /**
     * Load admin
     * - Css
     * - Javascript
     * - https://developer.wordpress.org/reference/functions/wp_enqueue_script/
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.11
     */
    public function load_scripts() 
    {
        $listar = Listar::get_instance();
        $assets_url = $listar->plugin_url().'/assets';

        // Remove cache by admin suffix time
        $version = $listar::$version;
        if(defined('WP_DEBUG') && WP_DEBUG) {
            $version .= '.'.time();
        }

        // Jquery UI Date picker & slider
        wp_enqueue_script('jquery-ui-core', false, ['jquery']);
        wp_enqueue_script('jquery-ui-datepicker', false, ['jquery']);
        wp_enqueue_script('jquery-ui-autocomplete', false, ['jquery']);
        wp_enqueue_script('jquery-ui-datepicker', false, ['jquery']);

        // Load standalone script
        wp_enqueue_script('listar-admin-booking-js', $assets_url . '/js/admin-booking.js', [], $version);

        // Variable
        wp_localize_script( 'listar-admin-booking-js', 'booking_vars', [
            'listing_url' => get_rest_url( null, 'listar/v1/place/list' ),
            'cart_url' => get_rest_url( null, 'listar/v1/booking/cart' ),
            'form_url' => get_rest_url( null, 'listar/v1/booking/form' ),
            'user_url' => get_rest_url( null, 'wp/v2/users' ),
        ]);
    }

    /**
     * Add metadata box
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.11
     */
    public function add()
    {
        add_meta_box(
            $this->post_type.'_meta_box', // Unique ID
            __('Booking Information', 'listar'), // Box title
            [$this, 'form'], // Content callback, must be of type callable
            [$this->post_type], // Post type
            'advanced',
            'high'
        );
    }

    /**
     * Render value & html
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @throws Exception
     * @since 1.0.11
     */
    public function form($post) 
    {
        $listar = Listar::get_instance();
        $booking = new Booking_Model();
        $booking_type = 'standard';
        $range_time = listar_get_range_time(0, 24, 2);
        $resource = NULL;

        // Timeslot default
        $booking_obj = new Booking();
        $booking_obj->set_booking_style('hourly');
        $time_slot = $booking_obj->booking_style->select_options();

        $support_fields = [
            'start_date'    => ['hidden' => FALSE, 'support' => 'standard|daily|hourly'],
            'start_time'    => ['hidden' => FALSE, 'support' => 'standard|daily'],
            'end_date'      => ['hidden' => FALSE, 'support' => 'daily'],
            'end_time'      => ['hidden' => FALSE, 'support' => 'daily'],
            'time_slot'     => ['hidden' => FALSE, 'support' => 'hourly']
        ];

        if($post && $post->ID) { // Edit case
            try {
                $booking->item($post->ID);
                $booking_type = $booking->get_booking_type();
                $resource =  $booking->get_single_resource();
                foreach($support_fields as $field => &$item) {
                    $item['hidden'] = !listar_booking_support_filed($item['support'], $field, $booking_type);
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $data = [];
            }
        }

        include_once $listar->plugin_path() . '/views/booking/form.php';
    }

    /**
     * Before save
     * @since 1.0.11
     * @param $post_id
     * @param $post_data
     */
    public function pre_post_update($post_id, $post_data) 
    {

    }

    /**
     * Customize list
     * @param WP_Query $query
     * @since 1.0.22
     */
    public function pre_get_posts(WP_Query $query) 
    {
        if(isset($_GET['post_type']) && $_GET['post_type'] == $this->post_type) {
            if (!listar_is_admin_user()) {
                //Get original meta query
                $meta_query = (array)$query->get('meta_query');
                // Add your criteria
                if(!isset($_GET['author'])) {
                    $meta_query[] = array(
                        'key' => '_author',
                        'value' => get_current_user_id(),
                        'type' => 'numeric',
                        'compare' => '=',
                    );
                }

                // Set the meta query to the complete, altered query
                $query->set('meta_query', $meta_query);
            }
        }
    }

    /**
     * @since 1.0.22
     */
    public function filter_list_table_counts($counts) 
    {
        if(isset($_GET['post_type']) && $_GET['post_type'] == $this->post_type) {
            if (!listar_is_admin_user()) {
                global $wpdb;
                $result = $wpdb->get_results($wpdb->prepare( "SELECT post_status, COUNT( * ) AS num_posts
                    FROM {$wpdb->prefix}posts
                    left join {$wpdb->prefix}postmeta on post_id = ID
                    WHERE post_type = {$this->post_type}
                    and meta_key = '_author' and meta_value = %d
                    GROUP BY post_status", get_current_user_id()));

                $counts = new \stdClass();
                if(!empty($result)) {
                    foreach($result as $status) {
                        $counts->{$status->post_status} = (int)$status->num_posts;
                    }
                }
            }
        }

        return $counts;
    }

    /**
     * Saving form data
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.11
     */
    public function save($post_id, $post, $update)
    {
        // Prevent auto save
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Prevent modify
        if(!$this->allow_modify()) {
            return;
        }

        // Filter again post status
        if($post->post_type === $this->post_type && !in_array($post->post_status, ['auto-draft', 'revision', 'trash'])) {
            try {
                //=== Booking Type
                $resource_id = absint($_POST['resource_id']);
                $booking_style = get_post_meta($resource_id, 'booking_style', TRUE);

                if(!$booking_style) {
                    throw new Exception(__('Undefined booking style', 'listar'));
                }

                $booking = new Booking($booking_style, $resource_id);
                $booking->set_created_via('admin'); // create booking via admin
                $booking->set_status($_REQUEST['post_status']);
                $booking->booking_style->initialize([
                    'person' => absint($_POST['person']),
                    'adult' => absint($_POST['adult']),
                    'children' => absint($_POST['children']),
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'],
                    'start_time' => $_POST['start_time'],
                    'end_time' => $_POST['end_time'],
                    'memo' => trim(sanitize_textarea_field($_POST['memo'])),
                ]);

                //=== Customer
                $customer = new Customer(absint($_POST['user_id']));
                $customer->initialize([
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'address' => $_POST['address']
                ]);

                //=== Cart
                // - Just support booking one item
                $cart = new Cart();
                $cart->set_data([
                    [
                        'id' => $booking->resource->get_id(),
                        'price' => $booking->resource->get_price(),
                        'name' => $booking->resource->get_name(),
                        'qty' => $booking->booking_style->qty(),
                        'options' => $booking->booking_style->options(),
                    ],
                ]);
                
                $booking->set_cart($cart);
                $booking->set_customer($customer);
                
                //=== Confirm Booking
                $booking->update_order($post_id);
            } catch (Exception $e) {
                Notice::admin_notice($e->getMessage());
                error_log("Exception:".$e->getMessage());
            } catch (\Throwable $e) {
                Notice::admin_notice($e->getMessage());
                error_log("Throwable:".$e->getMessage());
            }
        }
    }

    /**
     * - Fired on post add new
     * - Triggered at the end of the <body> section of a specific admin page
     * @since 1.0.11
     */
    public function admin_footer_post_new() 
    {
        global $post;
        if($post->post_type == $this->post_type) {
            $status_match = "";
            foreach (listar_booking_status() as $status => $item) {
                $name = $item['title'];
                $status_match .= "jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"{$status}\">{$name}</option>' );";
            }
            echo "<script>
                jQuery(document).ready( function() {
                    {$status_match}
                });
            </script>";
        }
    }

    /**
     * @since 1.0.22
     */
    public function admin_footer_edit()
    {
        global $post;

        if($post && $post->post_type == $this->post_type) {
            $status_match = "";
            foreach (listar_booking_status() as $status => $item) {
                $name = $item['title'];
                $status_match .= "jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"{$status}\">{$name}</option>' );";
            }
            echo "<script>
                jQuery(document).ready( function() {
                    {$status_match}
                });
            </script>";
        }
    }

    /**
     * - Fired on post edit page
     * - Triggered at the end of the <body> section of a specific admin page
     * @since 1.0.11
     */
    public function admin_footer_post() 
    {
        global $post;
        if($post->post_type == $this->post_type ) {
            $status_list = listar_booking_status();
            $status_name = isset($status_list[$post->post_status]) ? $status_list[$post->post_status]['title'] : __('Undefined', 'listar');
            $status_match = "";
            foreach ($status_list as $status => $item) {
                $name = $item['title'];
                $status_match .= "jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"{$status}\">{$name}</option>' );";
            }
            $status_match .= "jQuery( '#post-status-display' ).text( '{$status_name}' ); jQuery('select[name=\"post_status\"]' ).val('{$post->post_status}');";
            echo "<script>
            jQuery(document).ready( function() {
                {$status_match}
            });
            </script>";
        }
    }

    /**
     * Column filter
     * @since 1.0.11
     */
    public function booking_columns_filter( $columns ) 
    {
        $columns['cb'] = '<input type="checkbox" />';
        $columns['title'] = __( 'Title');
        $columns['date'] = __( 'Date');
        $columns['post_status'] = __( 'Status' );
        $columns['first_name'] = __( 'First Name' );
        $columns['last_name'] = __( 'Last Name' );
        $columns['phone'] = __( 'Phone' );
        $columns['order_total'] = __( 'Total' );
        unset($columns['comments']);
        return $columns;
    }

    /**
     * Column action
     * @since 1.0.11
     */
    public function booking_columns_action( $column, $post_id ) 
    {
        // Image column
        global $post;
        switch ($column) {
            case 'post_status';
                $status_list = listar_booking_status();
                $status_name = isset($status_list[$post->post_status]) ? $status_list[$post->post_status]['title'] : __('Undefined', 'listar');
                echo $status_name;
                break;
            case 'order_total':
                $order_currency = get_post_meta($post_id, '_order_currency', TRUE);
                echo Setting_Model::currency_format(get_post_meta($post_id, '_order_total', TRUE), $order_currency);
                break;
            case 'first_name':
                echo get_post_meta($post_id, '_billing_first_name', true);
                break;
            case 'last_name':
                echo get_post_meta($post_id, '_billing_last_name', true);
                break;
            case 'phone':
                echo get_post_meta($post_id, '_billing_phone', true);
                break;
        }
    }

    /**
     * Check only edit & update from admin then allow push notification
     * @return bool
     * @version 1.0.11
     */
    protected function allow_modify(): bool
    {
        $screen = get_current_screen();
        return $screen->id == $this->post_type && $screen->post_type === $this->post_type;
    }
}
