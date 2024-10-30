<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Category_Model;
use ListarWP\Plugin\Models\Location_Model;
use ListarWP\Plugin\Models\Feature_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Exception;

class Api_Setting_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    public function __construct() {
        $this->namespace = 'listar/v1';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/setting/init', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'init' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        register_rest_route( $this->namespace, '/setting/payment', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'payment' ],
                'permission_callback' => '__return_true',
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
     * @since 1.0.0
     */
    public function init($request) {
        // Categories
        $args = [
            'parent' => 0,
            'hide_empty' => 0,
            'orderby' => 'count', 
            'order' => 'DESC',
        ];
        if(($limit = Setting_Model::get_option('category_per_page')) > 0) {
            $args['number'] = $limit;
        }
        $categories = get_terms( Listar::$post_type.'_category', $args);        
        
        if(is_array($categories) && !empty($categories)) {
            foreach($categories as &$term) {
                Category_Model::assign_metadata($term);
            }
        }

        // Features
        $args = [
            'parent' => 0,
            'hide_empty' => 0,
            'orderby' => 'count', 
            'order' => 'DESC',
        ];
        if(($limit = Setting_Model::get_option('feature_per_page')) > 0) {
            $args['number'] = $limit;
        }
        $features = get_terms( Listar::$post_type.'_feature', $args);

        if(is_array($features) && !empty($features)) {
            foreach($features as &$term) {
                Feature_Model::assign_metadata($term);
            }
        }

        // Locations
        $locations = get_terms( Listar::$post_type.'_location', [
            'parent' => 0,
            'hide_empty' => 0
        ]);

        if(is_array($locations) && !empty($locations)) {
            foreach($locations as &$term) {
                Location_Model::assign_metadata($term);
            }
        }

        /**
         * View Options
         * @since 1.0.13
         */
        $view_setting = Setting_Model::get_options('option', 'view');
        $view_option = []; // final result
        $options = isset($view_setting['options']) ? $view_setting['options'] : [];
        if(!empty($options)) {
            foreach($options as $row) {
                $id = str_replace(Listar::$post_type.'_', '', $row['id']);
                $view_option[$id] = Setting_Model::get_view_option($id);
            }
        }

        // Basic Setting
        $settings = [
            'per_page' => (int) Setting_Model::get_option('per_page'),
            'color_option' => Setting_Model::get_color_option(),
            'unit_price' => Setting_Model::get_option('unit_price'),
            'price_min' => (int) Setting_Model::get_option('price_min'),
            'price_max' => (int) Setting_Model::get_option('price_max'),
            'time_min' => Setting_Model::get_option('time_min'),
            'time_max' => Setting_Model::get_option('time_max'),
            'list_mode' => Setting_Model::get_option('list_mode'),
            'submit_listing' => Setting_Model::submit_listing_use(),
            'layout_widget' => Setting_Model::get_option('layout_widget'),
            'layout_mode' => Setting_Model::get_option('layout_mode'),
        ];
        
        return rest_ensure_response([
            'success' => TRUE,
            'data' => [
                'categories' => $categories,
                'features' => $features,
                'locations' => $locations,
                'settings' => $settings,
                'view_option' => $view_option,
                'place_sort_option' => listar_get_sort_option(),
                'claim' => [
                    'claim_listing_use' => Setting_Model::get_option('claim_listing_use'),
                    'claim_button_text' => Setting_Model::get_option('claim_button_text')
                ]
            ]
        ]);
    }

    /**
     * Loading init form
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.30
     */
    public function payment($request) {
        try {
                    
            // Bank accounts for payment
            $bank_account_list = Setting_Model::get_option('bank_account_list');

            // Return endpoint data
            return rest_ensure_response([
                'success' => TRUE,
                'payment' => [
                    'use' => Setting_Model::payment_use(),
                    'term_condition_page' => get_site_url() . '/' . Setting_Model::get_option('payment_term_condition_page'),
                    'default' => 'paypal',
                    'list' => Setting_Model::payment_support_list(),
                    'bank_account_list' => $bank_account_list ? json_decode($bank_account_list) : []
                ]
            ]);
        } catch (Exception $e) {
            error_log("claim.form.failed: " . $e->getMessage());
            return new WP_Error('rest_invalid_post', $e->getMessage(), ['status' => 400]);
        }
    }
}
