<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Booking_Model;
use ListarWP\Plugin\Models\Comment_Model;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Location_Model;
use ListarWP\Plugin\Models\User_Model;
use WP_Post;
use WP_Comment;
use Exception;

class Admin_Listing_Controller {

    public function __construct() {
        // Customize post metadata
        add_action('add_meta_boxes', [$this, 'add']);

        // Handler when save post data (Fires once a post has been saved)
        add_action('save_post_listar', [$this, 'save'], 10, 3);

        // Check first time publish post
        add_action( 'transition_post_status', [$this, 'publish'], 10, 3 );

        // Loads admin scripts and styles
        if(is_edit_page()) {
            add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
        }

        /**
         * Customize display list
         * @since 1.0.15
         */
        add_filter( 'manage_listar_posts_columns', [$this, 'admin_columns_filter'] );
        add_action( 'manage_listar_posts_custom_column', [$this, 'admin_columns_action'], 10, 2);

        /**
         * Customize display list by user role
         */
        add_filter('pre_get_posts', [$this, 'pre_get_posts']);

        // Change status comment hook
        add_action('transition_comment_status', [$this, 'approve_comment_callback'], 10, 3);

        /**
         * Set default status 
         * - Status = pending
         * - User login is not admintrator
         * - Approval is enable
         *
         * @version 1.0.30
         */
        add_filter('wp_insert_post_data', [$this, 'insert_post_data'], 10, 4);
    }

    /**
     * Load admin
     * - Css
     * - Javascript
     * - https://developer.wordpress.org/reference/functions/wp_register_script/
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_scripts() {
        $listar = Listar::get_instance();
        $assets_url = $listar->plugin_url().'/assets';

        // Jquery UI Date picker & slider
        wp_enqueue_style( 'jquery-ui', $assets_url.'/css/jquery-ui-v1.12.1.css' );
        wp_enqueue_script('jquery-ui-core', false, ['jquery']);
        wp_enqueue_script('jquery-ui-slider', false, ['jquery']);
        wp_enqueue_script('jquery-ui-datepicker', false, ['jquery']);

        // Fontawesome
        wp_enqueue_style('fontawesome');
        wp_enqueue_style('fontawesome-iconpicker-css');
        wp_enqueue_script('fontawesome-iconpicker-js');

        // Color picker
        wp_enqueue_script( 'wp-color-picker');
        wp_enqueue_style( 'wp-color-picker' );

        // Google Map
        $map_use = Setting_Model::get_option("map_use");
        if($map_use) {
            wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key='
                .esc_attr(Setting_Model::get_option('gmap_key')).'&sensor=false&libraries=places');
        }

        // Load queued scripts
        wp_enqueue_style('listar-admin-css');
        wp_enqueue_script('listar-admin-js');

        // Load standalone script
        wp_enqueue_script('listar-admin-listar-js', $assets_url . '/js/admin-listing.js', [], $listar::$version);
    }

    /**
     * Add metadata box
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function add() {
        $listar = Listar::get_instance();

        add_meta_box(
            'listar_meta_box_booking', // Unique ID
            __('Booking Settings', 'listar'), // Box title
            [$this, 'form_booking'], // Content callback, must be of type callable
            [$listar::$post_type], // Post type
            'advanced',
            'high'
        );

        add_meta_box(
            'listar_meta_box_claim', // Unique ID
            __('Claim Settings', 'listar'), // Box title
            [$this, 'form_claim'], // Content callback, must be of type callable
            [$listar::$post_type], // Post type
            'advanced',
            'high'
        );

        add_meta_box(
            'listar_meta_box', // Unique ID
            __('Listing Information', 'listar'), // Box title
            [$this, 'form'], // Content callback, must be of type callable
            [$listar::$post_type], // Post type
            'advanced',
            'high'
        );

        add_meta_box(
            'listar_meta_box_schedule', // Unique ID
            __('Open Hours', 'listar'), // Box title
            [$this, 'form_schedule'], // Content callback, must be of type callable
            [$listar::$post_type], // Post type
            'side'
        );

        add_meta_box(
            'listar_meta_box_social_network', // Unique ID
            __('Social Network', 'listar'), // Box title
            [$this, 'form_social_network'], // Content callback, must be of type callable
            [$listar::$post_type], // Post type
            'side'
        );
    }

    /**
     * Form side schedule
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @throws Exception
     * @since 1.0.0
     */
    public function form_schedule($post) {
        $listar = Listar::get_instance();
        Place_Model::assign_metadata($post);
        $range_time = listar_get_range_time(0, 24, 2);
        $day_of_weeks = listar_get_open_hours_label();
        include_once $listar->plugin_path() . '/views/metadata/schedule.php';
    }

    /**
     * Form side social network
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function form_social_network($post) {
        $listar = Listar::get_instance();
        Place_Model::assign_metadata($post);
        include_once $listar->plugin_path() . '/views/metadata/social-network.php';
    }

    /**
     * Form booking
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function form_booking($post) {
        $listar = Listar::get_instance();
        Place_Model::assign_metadata($post);
        $booking_style_list = Booking_Model::get_booking_style_list();

        include_once $listar->plugin_path() . '/views/metadata/booking.php';
    }

    /**
     * Form claim
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.31
     */
    public function form_claim($post) {
        $listar = Listar::get_instance();
        $options = [
            [
                'value' => 'free',
                'title' => __('Claim for free', 'listar')
            ],
            [
                'value' => 'pay',
                'title' => __('Set a claim fee', 'listar'),
            ]
        ];

        include_once $listar->plugin_path() . '/views/metadata/claim.php';
    }

    /**
     * Render value & html
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @throws Exception
     * @since 1.0.0
     */
    public function form($post) {
        $listar = Listar::get_instance();

        Place_Model::assign_metadata($post);
        $countries  = get_terms( $listar::$post_type.'_location', [ 'parent' => 0, 'hide_empty' => 0]);
        $range_time = listar_get_range_time(0, 24, 2);
        $map_use = Setting_Model::get_option('map_use');

        if($post && $post->ID) {
            if($post->country) {
                $states = Location_Model::get_locations($post->country);
            }

            if($post->state) {
                $cities = Location_Model::get_locations($post->state);
            }
        }

        include_once $listar->plugin_path() . '/views/metadata/listing.php';
    }

    /**
     * Publish > first time
     *
     * @param string $new_status
     * @param string $old_status
     * @param WP_Post $post
     * @since 1.0.3
     */
    public function publish($new_status, $old_status, $post) {
        if(!$this->edit_screen()) {
            return;
        }

        if ($new_status !== 'publish' || $old_status === 'publish') {
            return;
        }

        if ($post->post_type !== Listar::$post_type) {
            return; // restrict the filter to a specific post type
        }

        // Check setting publish_new
        if (Setting_Model::push_notification_on() && Setting_Model::push_notification_crate_new()) {
            $device_token = User_Model::get_all_device_token();
            if (!empty($device_token)) {
                try {
                    // Get detail for push notification
                    Place_Model::assign_metadata($post);

                    // Send Notification
                    listar_send_device_notification_broadcast(
                        $device_token,
                        Setting_Model::push_notification_title($post, 'push_create_new_title'),
                        Setting_Model::push_notification_content($post, 'push_create_new_content'),
                        [
                            'id' => $post->ID,
                            'title' => $post->post_title,
                            'action' => 'create_post_listar',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'status' => 'done'
                        ]
                    );
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }

    /**
     * Update > Exist Data
     * @param int $post_id
     * @param WP_Post $post
     * @param bool $update
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function save($post_id, $post, $update) {
        // Set metadata by defined fields
        Place_Model::set_metadata($post_id, $_POST);
        
        /**
         * Check send push notification
         * - When has been modified
         * - The status is 'publish'
         */
        if(!$this->edit_screen()) {
            return;
        }

        // Check Settings > General > Mobile Settings >
        if($update && $post->post_status === 'publish') {
                // Check setting push notification
            if(Setting_Model::push_notification_on() && Setting_Model::push_notification_update_exit()) {
                $device_token = User_Model::get_all_device_token();
                if (!empty($device_token)) {
                    try {
                        // Get detail for push notification
                        Place_Model::assign_metadata($post);

                        // Send Notification
                        listar_send_device_notification_broadcast(
                            $device_token,
                            Setting_Model::push_notification_title($post, 'push_update_exist_title'),
                            Setting_Model::push_notification_content($post, 'push_update_exist_content'),
                            [
                                'id' => $post->ID,
                                'title' => $post->post_title,
                                'action' => 'update_post_listar',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                'status' => 'done'
                            ]
                        );
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Column filter
     * @since 1.0.15
     */
    public function admin_columns_filter( $columns ) {
        $columns = listar_array_insert_after('title', $columns, 'phone', __( 'Phone'));
        $columns = listar_array_insert_after('phone', $columns, 'email', __( 'Email'));
        $columns['image'] =__( 'Featured Image');        
        $columns['author_name'] =__( 'Author Name');        
        return $columns;
    }

    /**
     * Column action
     * @since 1.0.15
     */
    public function admin_columns_action( $column, $post_id ) {
        global $post;
        switch ($column) {
            case 'image':
                echo '<img style="max-width:80px" src="'.get_the_post_thumbnail_url($post_id, [150, 150]).'" />';
                break;
            case 'phone':  
                echo get_post_meta($post_id, 'phone', true);
                break;
            case 'email':  
                echo get_post_meta($post_id, 'email', true);
                break;    
            case 'author_name':  
                $userdata = get_userdata( $post->post_author );  
                echo '<a href="'. get_edit_user_link( $post->post_author ) .'">'. esc_attr( $userdata->display_name ) .'</a>';
                break;
        }
    }

    /**
     * Check limit list by the author
     * @since 1.0.15
     */
    public function pre_get_posts($query) {
        if($this->list_screen()) {
            $user = wp_get_current_user();
        }
    }

    /**
     * Handle when comment status  change
     * @param string $new_status
     * @param string $old_status
     * @param WP_Comment $comment
     * @since 1.0.20
     */
    public function approve_comment_callback($new_status, $old_status, WP_Comment $comment) {
        $post = get_post( (int) $comment->comment_post_ID);
        if($old_status != $new_status && $post->post_type == Listar::$post_type) {
            if($new_status == 'approved') {
                Comment_Model::set_rating_meta($post->ID);
            }
        }
    }

    /**
     * Check data insert
     *
     * @param array $post
     * @param array $postarr
     * @param array $unsanitized_postarr
     * @param bool $unsanitized_postarr
     * @return array
     * @version 1.0.30
     */
    public function insert_post_data($post, $postarr, $unsanitized_postarr, $update) {
        if($post['post_type'] === Listar::$post_type) {
            if(!listar_is_admin_user() && Setting_Model::submit_listing_approval_use()) {
                // Remove update status when send update
                if($update) {
                    if($post['post_status'] != 'trash') {
                        unset($post['post_status']);
                    }
                } else {
                    // Set default status
                    if ($post['post_status'] != 'trash') {
                        $post['post_status'] = 'pending';
                    }
                }
            }
        }

        return $post;
    }

    /**
     * Check only edit & update from admin then allow push notification
     * @return bool
     * @version 1.0.3
     */
    protected function edit_screen() {
        if ( !function_exists( 'get_current_screen' ) ) {
            require_once ABSPATH . '/wp-admin/includes/screen.php';
        }
        $screen = get_current_screen();
        return $screen && $screen->id == Listar::$post_type && $screen->post_type === Listar::$post_type;
    }

    /**
     * Check only edit & update from admin then allow push notification
     * @return bool
     * @version 1.0.15
     */
    protected function list_screen() {
        if ( !function_exists( 'get_current_screen' ) ) {
            require_once ABSPATH . '/wp-admin/includes/screen.php';
        }
        $screen = get_current_screen();
        return $screen && $screen->id == 'edit-'.Listar::$post_type && $screen->post_type === Listar::$post_type;
    }
}
