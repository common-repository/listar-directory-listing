<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Libraries\Api_Widget;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Category_Model;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Location_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Api_Home_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    protected $layout_mode = '';

    public function __construct() {
        $this->namespace = 'listar/v1';
        $this->layout_mode = Setting_Model::get_option('layout_mode');
    }
    

    public function register_routes() {
        register_rest_route( $this->namespace, '/home/init', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'init' ],
                'permission_callback' => '__return_true'
            ]
        ]);

        // @version 1.0.26
        register_rest_route( $this->namespace, '/home/widget', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'widget' ],
                'permission_callback' => '__return_true'
            ]
        ]);
    }


    /**
     * Default layout
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response*
     */
    public function init($request) {
        // Mobile > Home Slider
        $image_ids = Setting_Model::get_option('mobile_slider');
        $sliders = [];
        if($image_ids) {
            $ids = explode(',', $image_ids);
            foreach ($ids as $attachment_id) {
                // Get full image size
                $image = wp_get_attachment_image_src($attachment_id, 'full');
                if(!empty($image)) {
                    $sliders[] = $image[0]; // Get URL only
                }
            }
        }

        /**
         * Categories
         * - Default will get from setting  WP Admin > Appearance > Menu > Mobile Dashboard Categories
         * - If setting by Appearance > Menu then get all category data
         */
        $categories = Category_Model::get_mobile_menu('listar-mobile-dashboard-category');
        if(empty($categories)) {
            $categories = get_terms(Listar::$post_type.'_category', [
                'parent' => 0,
                'hide_empty' => 0
            ]);

            if(is_array($categories) && !empty($categories)) {
                foreach($categories as &$term) {
                    // Fix special character
                    $term->name = htmlspecialchars_decode($term->name);
                    Category_Model::assign_metadata($term);
                }
            }
        }

        /**
         * Popular Location
         * - Default will get from setting  WP Admin > Appearance > Menu > Mobile Dashboard Location
         * - If setting by Appearance > Menu then get all location data
         * - Limit default is 5
         */
        $locations = Location_Model::get_mobile_menu('listar-mobile-dashboard-location');
        if(empty($locations)) {
            $locations = get_terms(Listar::$post_type.'_location', [
                'parent' => 0,
                'hide_empty' => 0,
                'number' => 5,
            ]);

            if(is_array($locations) && !empty($locations)) {
                foreach($locations as &$term) {
                    $term->name = htmlspecialchars_decode($term->name);
                    Location_Model::assign_metadata($term);
                }
            }
        }

        // Recent Post
        $recent_posts = Place_Model::get_recent_data([
            'fields' => ['ID', 'post_title', 'post_date', 'post_date_gmt', 'post_author']
        ]);

        return rest_ensure_response([
            'success' => TRUE,
            'data' => [
                'sliders' => $sliders,
                'categories' => $categories,
                'locations' => $locations,
                'recent_posts' => $recent_posts
            ]
        ]);
    }

    /**
     * Get common setting data
     * - Basic setting
     * - Category data
     * - Location data
     * - Featured data
     * - Setting data
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public function widget($request) {

        $option = isset($request['option']) && $request['option'] > 0 ? $request['option'] : NULL;    
        if(!is_null($option)) {
            Api_Widget::set_option($option);
        }

        $data_widgets = Api_Widget::data_widgets();
        $select_option_widget = Setting_Model::get_option('select_option_widget');
        
        switch($select_option_widget) {
            case 'category':
                $options = Category_Model::get_mobile_menu('listar-mobile-dashboard-category');
                if(empty($options)) {
                    $options = get_terms(Listar::$post_type.'_category', [
                        'parent' => 0,
                        'hide_empty' => 0,
                    ]);
        
                    if(is_array($options) && !empty($options)) {
                        foreach($options as &$term) {
                            // Fix special character
                            $term->name = htmlspecialchars_decode($term->name);
                            Category_Model::assign_metadata($term);
                        }
                    }
                }
                break;       
            case 'location':
                $options = Location_Model::get_mobile_menu('listar-mobile-dashboard-location');
                if(empty($options)) {
                    $options = get_terms(Listar::$post_type.'_location', [
                        'parent' => 0,
                        'hide_empty' => 0,
                    ]);

                    if(is_array($options) && !empty($options)) {
                        foreach($options as &$term) {
                            $term->name = htmlspecialchars_decode($term->name);
                            Location_Model::assign_metadata($term);
                        }
                    }
                }
                break;       
            default:
                // Default get top options
                $options = get_terms(Listar::$post_type.'_location', [
                    'parent' => 0,
                    'hide_empty' => 0,
                ]);     
                break;       
        }

        // Header Setting
        $header = [
            'type' => Setting_Model::get_option('layout_widget_header'),
            'data' => []
        ];
        switch($header['type']) {
            case 'slider':
                break;
            case 'basic':
                // Mobile > Home Slider
                $image_ids = Setting_Model::get_option('mobile_slider');
                $sliders = [];
                if($image_ids) {
                    $ids = explode(',', $image_ids);
                    foreach ($ids as $attachment_id) {
                        // Get full image size
                        $image = wp_get_attachment_image_src($attachment_id, 'full');
                        if(!empty($image)) {
                            $sliders[] = $image[0]; // Get URL only
                        }
                    }
                } 
                $header['data'] = $sliders;
                break;    
        }
        
        
        return rest_ensure_response([
            'success' => TRUE,
            'data' => [
                'header' => $header,
                'widgets' => $data_widgets,
                'options' => $options
            ]
        ]);
    }
}
