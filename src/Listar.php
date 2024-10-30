<?php
namespace ListarWP\Plugin;

use ListarWP\Plugin\Controllers\Api_Ajax_Controller;
use WP_Post;
use WP_REST_Server;
use WP_User;
use WP_CLI;
use Exception;
use Throwable;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Comment_Model;
use ListarWP\Plugin\Models\User_Model;

/**
 * Listar class
 *
 * @class Listar The class that holds the entire Listar plugin
 */
class Listar {

    /**
     * Plugin version
     *
     * @var string
     */
    static $version = '1.0.35';

    /** Refers to a single instance of this class. */
    static $instance = null;

    /**
     * @var Loader
     */
    protected Loader $loader;

    /**
     * The plugin url
     *
     * @var string
     */
    public static $plugin_url;

    /**
     * The plugin path
     *
     * @var string
     */
    public static  $plugin_path;

    /**
     * The post type name
     *
     * @var string
     */
    static $post_type = 'listar';

    /**
     * Prefix setting
     * @var string
     */
    static $option_prefix = 'listar_';

    /**
     * Initializes the Listar() class
     *
     * Checks for an existing Listar() instance
     * and if it doesn't find one, creates it.
     */
    public function __construct() {
        // Define constants
        $this->define_constants();

        $this->loader = new Loader;

        // Load libs
        $this->load_libraries();

        $this->define_public_hooks();
    }

    /**
     * Creates or returns an instance of this class.
     *
     * @return  Listar A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @since 1.0.23
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'LISTAR_PREFIX', self::$option_prefix);
        define( 'LISTAR_VERSION', self::$version);
    }

    /**
     * Load libraries
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_libraries() {
        /**
         * Admin Controllers
         */
        if (is_admin()) {
            $admin_menu = ['listing', 'setting', 'claim', 'category', 'feature', 'location', 'import', 'export', 'booking', 'team', 'banner'];

            foreach($admin_menu as $menu) {
                // Register routes base on controller include
                try {
                    $class_name = __NAMESPACE__ . '\\Controllers\Admin_' . ucfirst($menu) . '_Controller';
                    new $class_name();
                } catch (Throwable $e) {
                    error_log($e->getMessage());
                }
            }
        }

        /**
         * Ajax 
         */
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX) {
            new Api_Ajax_Controller();
        }

        /**
         * Cli (Command)
         */
        if (php_sapi_name() == 'cli') {
            $src = LISTAR_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR.'Commands';
            if ($handle = opendir($src)) {
                while (false !== ($file_name = readdir($handle))) {
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    if ($file_name != "." && $file_name != ".." && $ext == 'php') {
                        $path_file_name = pathinfo($file_name, PATHINFO_FILENAME);
                        $class_name = __NAMESPACE__.'\\Commands\\'.$path_file_name;
                        $instance = new $class_name();
                        WP_CLI::add_command( $instance::command_name(), $instance );
                    }
                }
                closedir($handle);
            }
        }
    }

    /**
     * @since 1.0.23
     */
    public function define_public_hooks()
    {
        // Register script & style
        $this->loader->add_action('admin_enqueue_scripts', $this, 'load_scripts');

        // Load language
        $this->loader->add_action('init', $this, 'load_language');

        // Customize post types and taxonomies
        $this->loader->add_action('init', $this, 'register_post_type');
        $this->loader->add_action('init', $this, 'register_taxonomy');
        $this->loader->add_action('init', $this, 'register_menu');

        // Rest API init
        $this->loader->add_action( 'rest_api_init', $this, 'rest_api_init');

        // Customize user profile edit page
        $this->loader->add_action( 'edit_user_profile', $this, 'edit_user_profile_token' );
        $this->loader->add_action( 'show_user_profile', $this, 'edit_user_profile_token' );
        $this->loader->add_action( 'profile_update', $this, 'profile_update');

        // Customize admin head
        $this->loader->add_action( 'admin_head', $this, 'admin_head');

        // Customize admin_menu
        $this->loader->add_action( 'admin_menu', $this, 'admin_menu');

        // Check version support widgets
        if(wp_get_theme()->get('Name') == 'Listar WP' && wp_get_theme()->get('Version') < '2.0.0') {
            $this->load_widgets();
        } else {
            // Widgets
            $this->loader->add_action('widgets_init', $this, 'widgets_init');
        }

        // Called when plugin is activated
        register_activation_hook(__FILE__, [$this, 'activate']);

        // Called when plugin is updated completed
        $this->loader->add_action( 'upgrader_process_complete', $this, 'upgrade', 10, 2 );

        // Comment callback handler
        try {
            $comment_obj = new Comment_Model();
            $this->loader->add_action('wp_insert_comment', $comment_obj, 'after_save_comment');
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }

        // Hook
        $hook = 'claim';
        $class_name = __NAMESPACE__ . '\\Hooks\\' . ucfirst($hook);

        new $class_name();
    }

    /**
     * Load widget
     * > Only support Listar WP theme version 1.x
     * > Since version 2x, Widget will be loaded by Listar WP theme
     * @version 1.0.23
     */
    public function load_widgets()
    {
        /**
         * Widget
         */
        include_once LISTAR_PATH . '/class/widgets/class-listar-location-item-widget.php';
        include_once LISTAR_PATH . '/class/widgets/class-listar-post-item-widget.php';
        include_once LISTAR_PATH . '/class/widgets/class-listar-directory-item-widget.php';
        include_once LISTAR_PATH . '/class/widgets/class-listar-directory-view-widget.php';
        include_once LISTAR_PATH . '/class/widgets/class-listar-category-item-widget.php';
        include_once LISTAR_PATH . '/class/widgets/class-listar-category-post-widget.php';
        include_once LISTAR_PATH . '/class/class-listar-setting-model.php';

        add_action('widgets_init', function () {
            register_widget('Listar_Location_Item_Widget');
            register_widget('Listar_Post_Item_Widget');
            register_widget('Listar_Directory_Item_Widget');
            register_widget('Listar_Directory_View_Widget');
            register_widget('Listar_Category_Item_Widget');
            register_widget('Listar_Category_Post_Widget');
        });
    }

    /**
     * Register widgets
     * @since v1.0.23
     */
    public function widgets_init()
    {
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\Data');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\Related');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\Category');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\CategoryMenu');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\Location');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\LocationMenu');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\FeatureMenu');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\PriceRange');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\RatingRange');
        register_widget(__NAMESPACE__ . '\\Widgets\Listing\SearchForm');
        register_widget(__NAMESPACE__ . '\\Widgets\Post\Recent');
        register_widget(__NAMESPACE__ . '\\Widgets\Post\Category');
        register_widget(__NAMESPACE__ . '\\Widgets\Post\Related');

        // --------- SIDEBAR
        // Api widget
        register_widget(__NAMESPACE__ . '\\Widgets\Api\Location');
        register_widget(__NAMESPACE__ . '\\Widgets\Api\Category');
        register_widget(__NAMESPACE__ . '\\Widgets\Api\Banner');
        register_widget(__NAMESPACE__ . '\\Widgets\Api\Post');

        // Mobile home sidebar
        register_sidebar([
            'name' => __('[Listar] Mobile Home', 'listar'),
            'id' => 'listar-mobile-home-sidebar',
            'before_title' => '',
            'after_title' => '',
            'before_widget' => '',
            'after_widget' => '',
        ]);
    }

    /**
     * Load admin scripts needed
     * - Just register script only
     * - Only load when need
     * - Css
     * - Javascript
     * - https://developer.wordpress.org/reference/functions/wp_register_script/
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_scripts() {
        $assets_url = self::plugin_url().'/assets';

        // Remove cache by admin suffix time
        $version = self::$version;
        if(defined('WP_DEBUG') && WP_DEBUG) {
            $version .= '.'.time();
        }

        /**
         * Fontawesome
         * https://www.jqueryscript.net/other/Simple-FontAwesome-Icon-Picker-Plugin-Bootstrap.html
         */
        wp_register_style('fontawesome', $assets_url . '/css/fontawesome-5.14.0/css/all.css', [], $version);
        wp_register_style('fontawesome-iconpicker-css', $assets_url . '/css/fontawesome-iconpicker.min.css', [], $version);
        wp_register_script('fontawesome-iconpicker-js', $assets_url . '/js/fontawesome-iconpicker.min.js', [], $version);

        /**
         * Common Scripts
         */
        wp_register_style('listar-admin-css', $assets_url . '/css/admin.css', [], $version);
        wp_enqueue_script('listar-admin-js', $assets_url . '/js/admin.js', [], $version);

        /**
         * Variable
         */
        wp_localize_script( 'listar-admin-js', 'listar_vars', [
            'admin_ajax' => admin_url( 'admin-ajax.php' ),
            'option' => [
                'price_max' => (int) Setting_Model::get_option('price_max'),
                'price_min' => (int) Setting_Model::get_option('price_min'),
                'color_option' => Setting_Model::get_color_option(),
                'map_use' => Setting_Model::get_option('map_use'),
                'map_center' => [Setting_Model::get_option('gmap_center_lat'),
                    Setting_Model::get_option('gmap_center_long')],
                'map_zoom' => (int) Setting_Model::get_option('gmap_zoom'),
            ]
        ]);
    }

    /**
     * Init reset API
     *
     * @param WP_REST_Server $server Server request data
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function rest_api_init($server) {
        // Define endpoints
        $endpoints = ['home', 'post' ,'category', 'place', 'location', 'wishlist', 'comment', 'setting', 'auth', 'author', 'booking', 'claim'];

        // Loop register routers
        foreach($endpoints as $endpoint) {
            try {
                $class_name = __NAMESPACE__ . '\\Controllers\Api_' . ucfirst($endpoint) . '_Controller';
                $controller = new $class_name();
                $controller->register_routes();
            } catch (Throwable $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * Render customize user's token device
     * @param WP_User $user
     */
    public function edit_user_profile_token($user) {
        $tokens = get_user_meta($user->ID, User_Model::$token_device_key);
        $is_blocked = User_Model::is_blocked($user);
        include_once self::plugin_path() . '/views/user/profile.php';
    }

    /**
     * Handle user update profile action
     * @param int $user_id
     * @since 1.0.9
     */
    public function profile_update($user_id) {
        if( current_user_can('edit_users') ) {
            // Customize user photo
            $user_photo_key = User_Model::$user_photo_key;
            if(isset($_POST[$user_photo_key])) {
                $image_id = !empty($_POST[$user_photo_key]) ? absint($_POST[$user_photo_key]) : '';
                update_user_meta($user_id, $user_photo_key, $image_id);
            }

            if(isset($_POST['listar_block_account'])) {
                User_Model::block_account($user_id);
            } else {
                User_Model::unblock_account($user_id);
            }
        }
    }

    /**
     * Customize remove_publishing_actions
     * @param int $user_id
     * @since 1.0.30
     */
    public function admin_head() 
    {        
        global $typenow;
        if ( $typenow == self::$post_type && Setting_Model::submit_listing_approval_use() && !listar_is_admin_user()) { ?>
            <style>
                .misc-pub-section,
                .misc-pub-post-status,
                .misc-pub-visibility,
                .inline-edit-status,
                .edit-post-post-visibility,
                .edit-post-post-schedule  {
                    display: none !important;
                }
            </style>
            <script>
                jQuery(document).ready(function($){
                    $('.misc-pub-section').remove();
                    $('.misc-pub-post-status').remove();
                    $('.misc-pub-visibility').remove();
                    $('.inline-edit-status').remove();
                    $('.edit-post-post-visibility"').remove();
                    $('.edit-post-post-schedule"').remove();
                });
            </script>   
        <?php }
    }

    /**
     * Customize admin menu
     * @since 1.0.30
     */
    public function admin_menu()
    {
        /**
         * Hide media menu option
         */
        if(!listar_is_admin_user() && Setting_Model::get_option('hide_media')) {
            remove_menu_page('upload.php');
        }
    }
    
    /**
     * Called when plugin is activated
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function activate() {
        if (is_admin()) {
            // Install default settings
            Setting_Model::install();
        }

        // rewrite rules problem, register and flush
        $this->register_post_type();
        $this->register_taxonomy();
        $this->register_menu();

        flush_rewrite_rules();

        update_option('listar_installed', time());
        update_option('listar_version', self::$version);

        // Create tables
        Installer::create_tables();
    }

    /**
     * This function runs when WordPress completes its upgrade process
     * It iterates through each plugin updated to see if ours is included
     * @param $upgrader_object
     * @param $options
     */
    public function upgrade( $upgrader_object, $options ) {

        // The path to our plugin's main file
        $listar_plugin = plugin_basename( __FILE__ );

        // If an update has taken place and the updated type is plugins and the plugins element exists
        if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            // Iterate through the plugins being updated and check if ours is there
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == $listar_plugin ) {
                    // Your action if it is your plugin
                    Installer::create_tables();
                }
            }
        }
    }

    /**
     * Load language
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_language() {
        load_plugin_textdomain('listar', false, LISTAR_PATH . '/languages/');
    }

    /**
     * Register post type
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function register_post_type() {
        // Support fields
        $support = ['title', 'excerpt', 'thumbnail', 'comments'];

        // Make sure have thumbnail
        add_theme_support( 'post-thumbnails' );

        // --- Listing Directory
        $args = [
            'labels' => [
                'name'                => _x('Listings', 'Post Type General Name', 'listar'),
                'singular_name'       => _x('Listing', 'Post Type Singular Name', 'listar'),
                'menu_name'           => Setting_Model::get_option('label'),
                'parent_item_colon'   => __('Parent Listing', 'listar'),
                'all_items'           => __('All Listings', 'listar'),
                'view_item'           => __('View Listing', 'listar'),
                'add_new_item'        => __('Add Listing', 'listar'),
                'add_new'             => __('Add New', 'listar'),
                'edit_item'           => __('Edit Listing', 'listar'),
                'update_item'         => __('Update Listing', 'listar'),
                'search_items'        => __('Search Listing', 'listar'),
                'not_found'           => __('Not listing found', 'listar'),
                'not_found_in_trash'  => __('Not found in Trash', 'listar'),
            ],
            'supports'            => $support,
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-star-filled',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_in_rest'        => true,
            'rewrite'             => ['slug' => Setting_Model::get_option('rewrite')],
            'capability_type'     => 'post',
            'taxonomies'          => ['post_tag']
        ];

        register_post_type(self::$post_type, $args);

        // --- Booking
        $args = [
            'labels' => [
                'name'                => _x('Bookings', 'Post Type General Name', 'listar'),
                'singular_name'       => _x('Booking', 'Post Type Singular Name', 'listar'),
                'menu_name'           => __('Booking', 'listar'),
                'parent_item_colon'   => __('Parent Booking', 'listar'),
                'all_items'           => __('Bookings', 'listar'),
                'view_item'           => __('View Booking', 'listar'),
                'add_new_item'        => __('Add Booking', 'listar'),
                'add_new'             => __('Add New', 'listar'),
                'edit_item'           => __('Edit Booking', 'listar'),
                'update_item'         => __('Update Booking', 'listar'),
                'search_items'        => __('Search Booking', 'listar'),
                'not_found'           => __('Not booking found', 'listar'),
                'not_found_in_trash'  => __('Not found in Trash', 'listar'),
            ],
            'supports'            => ['title'],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type='.self::$post_type,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'menu_position'       => 6,
            'menu_icon'           => 'dashicons-calendar',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_in_rest'        => true,
            'rewrite'             => ['slug' => Setting_Model::get_option('booking_rewrite')],
            'capability_type'     => 'post'
        ];

        register_post_type(self::$post_type.'_booking', $args);

        /**
         * Customize display list > Navigation post status
         * - Support customize post status navigation bar (admin)
         * - Support customize query
         */
        foreach (listar_booking_status() as $status => $item) {
            register_post_status($status, array(
                'label' => $item['title'],
                'post_type' => [self::$post_type.'_booking'],
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'show_in_metabox_dropdown' => true,
                'show_in_inline_dropdown' => true,
                'label_count' => _n_noop($item['title'].' <span class="count">(%s)</span>', $item['title'].' <span class="count">(%s)</span>'),
            ));
        }   

        // --- Claim listing
        $args = [
            'labels' => [
                'name'                => _x('Claim Listings', 'Post Type General Name', 'listar'),
                'singular_name'       => _x('Claim', 'Post Type Singular Name', 'listar'),
                'menu_name'           => __('Claim', 'listar'),
                'parent_item_colon'   => __('Parent Claim', 'listar'),
                'all_items'           => __('Claim Listings', 'listar'),
                'view_item'           => __('View Claim', 'listar'),
                'add_new_item'        => __('Add Claim', 'listar'),
                'add_new'             => __('Add New', 'listar'),
                'edit_item'           => __('Edit Claim', 'listar'),
                'update_item'         => __('Update Claim', 'listar'),
                'search_items'        => __('Search Claim', 'listar'),
                'not_found'           => __('Not claim found', 'listar'),
                'not_found_in_trash'  => __('Not claim in Trash', 'listar'),
            ],
            'supports'            => ['title'],
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type='.self::$post_type,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'menu_position'       => 6,
            'menu_icon'           => 'dashicons-calendar',
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_in_rest'        => true,
            'capability_type'     => 'post'
        ];

        register_post_type(self::$post_type.'_claim', $args);

        /**
         * Customize display list > Navigation post status
         * - Support customize post status navigation bar (admin)
         * - Support customize query
         */
        foreach (listar_claim_status() as $status => $item) {
            register_post_status($status, array(
                'label' => $item['title'],
                'post_type' => [self::$post_type.'_claim'],
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'show_in_metabox_dropdown' => true,
                'show_in_inline_dropdown' => true,
                'label_count' => _n_noop($item['title'].' <span class="count">(%s)</span>', $item['title'].' <span class="count">(%s)</span>'),
            ));
        } 

        // Only show for admin
        if(listar_is_admin_user()) {
            // --- Team
            $args = [
                'labels' => [
                    'name'                => _x('Teams', 'Post Type General Name', 'listar'),
                    'singular_name'       => _x('Team', 'Post Type Singular Name', 'listar'),
                    'menu_name'           => __('Teams', 'listar'),
                    'parent_item_colon'   => __('Parent Team', 'listar'),
                    'all_items'           => __('Team Member', 'listar'),
                    'view_item'           => __('View Member', 'listar'),
                    'add_new_item'        => __('Add Member', 'listar'),
                    'add_new'             => __('Add New', 'listar'),
                    'edit_item'           => __('Edit Member', 'listar'),
                    'update_item'         => __('Update Member', 'listar'),
                    'search_items'        => __('Search Member', 'listar'),
                    'not_found'           => __('Not member found', 'listar'),
                    'not_found_in_trash'  => __('Not found in Trash', 'listar'),
                ],
                'supports'            => ['title', 'excerpt', 'thumbnail', 'page-attributes'],
                'hierarchical'        => false,
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => 'edit.php?post_type='.self::$post_type,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'menu_position'       => 8,
                'menu_icon'           => 'dashicons-admin-users',
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => true,
                'publicly_queryable'  => true,
                'show_in_rest'        => true,
                'rewrite'             => ['slug' => Setting_Model::get_option('team_rewrite')],
                'capability_type'     => 'post'
            ];

            register_post_type(self::$post_type.'_team', $args);

            // --- Banner
            $args = [
                'labels' => [
                    'name'                => _x('Banner Ads', 'Post Type General Name', 'listar'),
                    'singular_name'       => _x('Banner Ads', 'Post Type Singular Name', 'listar'),
                    'menu_name'           => __('Banner Ads', 'listar'),
                    'parent_item_colon'   => __('Parent Banner Ads', 'listar'),
                    'all_items'           => __('Banner Ads', 'listar'),
                    'view_item'           => __('View Banner Ads', 'listar'),
                    'add_new_item'        => __('Add Banner Ads', 'listar'),
                    'add_new'             => __('Add New', 'listar'),
                    'edit_item'           => __('Edit Banner Ads', 'listar'),
                    'update_item'         => __('Update Banner Ads', 'listar'),
                    'search_items'        => __('Search Banner Ads', 'listar'),
                    'not_found'           => __('Not booking found', 'listar'),
                    'not_found_in_trash'  => __('Not found in Trash', 'listar'),
                ],
                'supports'            => ['title', 'excerpt', 'thumbnail', 'editor', 'page-attributes'],
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => 'edit.php?post_type='.self::$post_type,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'menu_position'       => 7,
                'menu_icon'           => 'dashicons-schedule',
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => true,
                'publicly_queryable'  => false,
                'show_in_rest'        => false,
                'capability_type'     => 'post'
            ];

            register_post_type(self::$post_type.'_banner', $args);
        }
    }

    /**
     * Register tags taxonomy
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function register_taxonomy() {
        // Category
        $args = [
            'labels' => [
                'name'                       => _x('Listing Categories', 'Taxonomy General Name', 'listar'),
                'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'listar'),
                'menu_name'                  => __('Categories', 'listar'),
                'all_items'                  => __('All categories', 'listar'),
                'parent_item'                => __('Parent Category', 'listar'),
                'parent_item_colon'          => __('Parent Category:', 'listar'),
                'new_item_name'              => __('New Category', 'listar'),
                'add_new_item'               => __('Add New Category', 'listar'),
                'edit_item'                  => __('Edit Category', 'listar'),
                'update_item'                => __('Update Category', 'listar'),
                'view_item'                  => __('View Category', 'listar'),
                'separate_items_with_commas' => __('Separate items with commas', 'listar'),
                'add_or_remove_items'        => __('Add or Remove Category', 'listar'),
                'choose_from_most_used'      => __('Choose from the most used', 'listar'),
                'popular_items'              => __('Popular Category', 'listar'),
                'search_items'               => __('Search Category', 'listar'),
                'not_found'                  => __('Not Found', 'listar'),
                'no_terms'                   => __('No categories', 'listar'),
                'items_list'                 => __('Category list', 'listar'),
                'items_list_navigation'      => __('Category list navigation', 'listar'),
            ],
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => [
                'slug' => Setting_Model::get_option('category_rewrite')
            ]
        ];

        register_taxonomy(self::$post_type . '_category', [self::$post_type], $args);

        // Features
        $args = [
            'labels'                        => [
                'name'                       => _x('Features', 'Taxonomy General Name', 'listar'),
                'singular_name'              => _x('Feature', 'Taxonomy Singular Name', 'listar'),
                'menu_name'                  => __('Features', 'listar'),
                'all_items'                  => __('All features', 'listar'),
                'parent_item'                => __('Parent Feature', 'listar'),
                'parent_item_colon'          => __('Parent Feature:', 'listar'),
                'new_item_name'              => __('New Feature', 'listar'),
                'add_new_item'               => __('Add New Feature', 'listar'),
                'edit_item'                  => __('Edit Feature', 'listar'),
                'update_item'                => __('Update Feature', 'listar'),
                'view_item'                  => __('View Feature', 'listar'),
                'separate_items_with_commas' => __('Separate items with commas', 'listar'),
                'add_or_remove_items'        => __('Add or Remove Feature', 'listar'),
                'choose_from_most_used'      => __('Choose from the most used', 'listar'),
                'popular_items'              => __('Popular Feature', 'listar'),
                'search_items'               => __('Search Features', 'listar'),
                'not_found'                  => __('Not Found', 'listar'),
                'no_terms'                   => __('No features', 'listar'),
                'items_list'                 => __('Feature list', 'listar'),
                'items_list_navigation'      => __('Feature list navigation', 'listar'),
            ],
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => false,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rewrite'                    => [
                'slug' => Setting_Model::get_option('feature_rewrite')
            ]
        ];

        register_taxonomy(self::$post_type . '_feature', [self::$post_type], $args);

        // Location: Country/City/State
        $args = [
            'labels' => [
                'name'                       => _x('Locations', 'Taxonomy General Name', 'listar'),
                'singular_name'              => _x('Location', 'Taxonomy Singular Name', 'listar'),
                'menu_name'                  => __('Locations', 'listar'),
                'all_items'                  => __('All Locations', 'listar'),
                'parent_item'                => __('Parent Location', 'listar'),
                'parent_item_colon'          => __('Parent Location:', 'listar'),
                'new_item_name'              => __('New Location', 'listar'),
                'add_new_item'               => __('Add New Location', 'listar'),
                'edit_item'                  => __('Edit Location', 'listar'),
                'update_item'                => __('Update Location', 'listar'),
                'view_item'                  => __('View Location', 'listar'),
                'separate_items_with_commas' => __('Separate items with commas', 'listar'),
                'add_or_remove_items'        => __('Add or Remove Location', 'listar'),
                'choose_from_most_used'      => __('Choose from the most used', 'listar'),
                'popular_items'              => __('Popular Location', 'listar'),
                'search_items'               => __('Search Location', 'listar'),
                'not_found'                  => __('Not Found', 'listar'),
                'no_terms'                   => __('No categories', 'listar'),
                'items_list'                 => __('Location list', 'listar'),
                'items_list_navigation'      => __('Location list navigation', 'listar'),
            ],
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_in_menu'               => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'meta_box_cb'                => false, // Make sure user only select from Country/State/City (dropdown)
            'rewrite'                    => [
                'slug' => Setting_Model::get_option('location_rewrite')
            ]
        ];

        register_taxonomy(self::$post_type . '_location', [self::$post_type], $args);

        // Banner Category
        $args = [
            'labels' => [
                'name'                       => _x('Banner Ads Categories', 'Taxonomy General Name', 'listar'),
                'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'listar'),
                'menu_name'                  => __('Categories', 'listar'),
                'all_items'                  => __('All categories', 'listar'),
                'parent_item'                => __('Parent Category', 'listar'),
                'parent_item_colon'          => __('Parent Category:', 'listar'),
                'new_item_name'              => __('New Category', 'listar'),
                'add_new_item'               => __('Add New Category', 'listar'),
                'edit_item'                  => __('Edit Category', 'listar'),
                'update_item'                => __('Update Category', 'listar'),
                'view_item'                  => __('View Category', 'listar'),
                'separate_items_with_commas' => __('Separate items with commas', 'listar'),
                'add_or_remove_items'        => __('Add or Remove Category', 'listar'),
                'choose_from_most_used'      => __('Choose from the most used', 'listar'),
                'popular_items'              => __('Popular Category', 'listar'),
                'search_items'               => __('Search Category', 'listar'),
                'not_found'                  => __('Not Found', 'listar'),
                'no_terms'                   => __('No categories', 'listar'),
                'items_list'                 => __('Category list', 'listar'),
                'items_list_navigation'      => __('Category list navigation', 'listar'),
            ],
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        ];

        register_taxonomy(self::$post_type . '_banner_category', [self::$post_type.'_banner'], $args);
    }

    /**
     * Register menu
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function register_menu() {
        // Mobile > Dashboard > Popular Locations
        register_nav_menu('listar-mobile-dashboard-location',__('Mobile Dashboard Location', 'listar'));

        // Mobile > Dashboard >  Categories
        register_nav_menu('listar-mobile-dashboard-category',__('Mobile Dashboard Category', 'listar'));
    }

    /**
     * Check post type match with define
     * @param int $post_id
     * @return WP_POST
     * @author Paul <paul.passionui@gmail.com>
     * @throws Exception
     */
    static function valid_post($post_id) {
        $post_id = absint($post_id);
        if ( $post_id <= 0 ) {
            throw new Exception(__( 'Invalid ID.', 'listar'));
        }

        $post = get_post( (int) $post_id);

        if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== Listar::$post_type) {
            throw new Exception(__( 'Invalid data.', 'listar'));
        }

        return $post;
    }

    /**
     * Get the plugin url.
     *
     * @return string
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function plugin_url() {
        if (self::$plugin_url) {
            return self::$plugin_url;
        }

        return self::$plugin_url = LISTAR_PLUGIN_URL;
    }

    /**
     * Get the plugin path.
     *
     * @return string
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function plugin_path() {
        if (self::$plugin_path) return self::$plugin_path;

        return self::$plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }
} // Listar
