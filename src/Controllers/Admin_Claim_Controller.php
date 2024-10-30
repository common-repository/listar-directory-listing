<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Claim_Model;
use WP_Post;
use Exception;
use WP_Query;
use stdClass;

class Admin_Claim_Controller
{
    static $page = 'claim';

    /**
     * Post type support
     * @var string
     */
    protected $post_type = '';

    public function __construct()
    {
        $this->post_type = Claim_Model::post_type();

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

        // Customize post status
        add_action('admin_footer-edit.php', [$this, 'admin_footer_edit']);
        add_action('admin_footer-post.php', [$this, 'admin_footer_post']);
        add_action('admin_footer-post-new.php', [$this, 'admin_footer_post_new']);

        /**
         * Customize display list
         * @since 1.0.11
         */
        add_filter( 'manage_'.$this->post_type.'_posts_columns', [$this, 'claim_columns_filter'] );
        add_action( 'manage_'.$this->post_type.'_posts_custom_column', [$this, 'claim_columns_action'], 10, 2);    
    }

    /**
     * Load script when load admin apge
     * @since 1.0.30
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
            __('Claim Information', 'listar'), // Box title
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
     * @since 1.0.30
     */
    public function form($post) {
        $listar = Listar::get_instance();
        $claim = new Claim_Model();

         // Edit case
        if($post && $post->ID) {
            try {
                $claim->item($post->ID);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
        }

        include_once $listar->plugin_path() . '/views/claim/form.php';
    }

    /**
     * Show only my claim listing if i am not admin
     * @param WP_Query $query
     * @since 1.0.30
     */
    public function pre_get_posts(WP_Query $query) {
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
    public function filter_list_table_counts($counts) {
        if(isset($_GET['post_type']) && $_GET['post_type'] == 'listar_claim') {
            if (!listar_is_admin_user()) {
                global $wpdb;
                $result = $wpdb->get_results($wpdb->prepare( "SELECT post_status, COUNT( * ) AS num_posts
                    FROM {$wpdb->prefix}posts
                    left join {$wpdb->prefix}postmeta on post_id = ID
                    WHERE post_type = 'listar_claim'
                    and meta_key = '_author' and meta_value = %d
                    GROUP BY post_status", get_current_user_id()));

                $counts = new stdClass();
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
     * - Fired on post add new
     * - Triggered at the end of the <body> section of a specific admin page
     * @since 1.0.11
     */
    public function admin_footer_post_new() {
        global $post;
        if($post->post_type == $this->post_type) {
            $status_match = "";
            foreach (listar_claim_status() as $status => $item) {
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
     * @since 1.0.30
     */
    public function admin_footer_edit()
    {
        global $post;

        if($post && $post->post_type == $this->post_type) {
            $status_match = "";
            foreach (listar_claim_status() as $status => $item) {
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
    public function admin_footer_post() {
        global $post;
        if($post->post_type == $this->post_type ) {
            $status_list = listar_claim_status();
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
     * @since 1.0.30
     */
    public function claim_columns_filter( $columns ) {
        $columns['cb'] = '<input type="checkbox" />';
        $columns['title'] = __('Title');
        $columns['date'] = __('Date');
        $columns['post_status'] = __('Status');
        $columns['first_name'] = __('First Name');
        $columns['last_name'] = __('Last Name');
        $columns['phone'] = __('Phone');
        $columns['method_charge'] = __('Method Charge');
        $columns['claim_price'] = __('Fee');
        unset($columns['comments']);
        return $columns;
    }

    /**
     * Column action
     * @since 1.0.30
     */
    public function claim_columns_action( $column, $post_id ) {
        // Image column
        global $post;
        switch ($column) {
            case 'post_status';
                $status_list = listar_claim_status();
                if(isset($status_list[$post->post_status])) {
                    $status_name = $status_list[$post->post_status]['title'];
                    $status_color = $status_list[$post->post_status]['color'];
                } else {
                    $status_name = __('Undefined', 'listar');
                    $status_color = '#333';
                }
                
                echo '<span style="color:'.$status_color.'">'.$status_name.'</span>';
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
            case 'method_charge':
                $methods = listar_claim_method_charges();
                $claim_method_charge = get_post_meta($post_id, 'claim_method_charge', true);
                echo isset($methods[$claim_method_charge]) ? $methods[$claim_method_charge]['title'] : __('Undefined');
                break;
            case 'claim_price':
                $order_currency = get_post_meta($post_id, 'claim_unit_price', TRUE);
                $price = get_post_meta($post_id, 'claim_price', TRUE);
                echo $price > 0 ? Setting_Model::currency_format($price, $order_currency) : '';
                break;    
        }
    }

    /**
     * Check only edit & update from admin
     * @return bool
     * @version 1.0.30
     */
    protected function allow_modify(): bool
    {
        $screen = get_current_screen();
        return $screen->id == $this->post_type && $screen->post_type === $this->post_type;
    }
}