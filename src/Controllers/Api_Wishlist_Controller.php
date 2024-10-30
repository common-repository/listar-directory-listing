<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Wishlist_Model;
use ListarWP\Plugin\Models\Place_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;
use WP_Error;
use WP_Query;
use Exception;

class Api_Wishlist_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    /**
     * @var WP_User | Object
     */
    protected $user;

    public function __construct() {
        $this->namespace = 'listar/v1';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/wishlist/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => [ $this, 'permission_callback' ],
                'args'     => [
                    'page' => [
                        'description'       => __( 'Current page of the collection.', 'listar' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1,
                    ],
                    'per_page' => [
                        'description'       => __( 'Maximum number of items to be returned in result set.', 'listar' ),
                        'type'              => 'integer',
                        'default'           => (int) get_option( 'posts_per_page' ),
                        'minimum'           => 1,
                        'maximum'           => 100,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                    ],
                ]
            ]
        ]);

        register_rest_route( $this->namespace, '/wishlist/save', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'save' ],
                'permission_callback' => [ $this, 'permission_callback' ],
                'args'     => [
                    'post_id' => [
                        'description'       => __( 'Post ID.', 'listar' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1,
                    ],
                ]
            ]
        ]);

        register_rest_route( $this->namespace, '/wishlist/remove', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'remove' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);

        register_rest_route( $this->namespace, '/wishlist/reset', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'permission_callback' => [ $this, 'permission_callback' ],
                'callback' => [ $this, 'reset' ],
            ]
        ]);
    }

    /**
     * Check token permssion
     *
     * @return bool|WP_Error
     * @since 1.0.0
     */
    public function permission_callback() {
        $current_user_id = get_current_user_id();
        if ( empty( $current_user_id ) ) {
            return new WP_Error(
                'rest_permission',
                __( 'You are not currently logged in.' ),
                array( 'status' => 200 )
            );
        }
        // Current user logged in
        $user = wp_get_current_user();
        $this->user = $user->data;

        return TRUE;
    }

    /**
     * Get list data
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        $post_ids = get_user_meta($this->user->ID, Wishlist_Model::$meta_key);
        $per_page = isset($request['per_page']) ? absint($request['per_page'])
            : absint(Setting_Model::get_option('per_page'));

        $args = [
            'post_type'         => Listar::$post_type,
            'post_status'       => 'publish',
            'post__in'          => !empty($post_ids) ? $post_ids : [0],
            'paged'             =>  isset($request['page']) ? absint($request['page']) : 1,
            'posts_per_page'    => $per_page
        ];

        $query  = new WP_Query($args);
        $posts  = $query->get_posts();

        $page        = absint($args['paged']);
        $total_posts = absint($query->found_posts);

        if ( $total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $args );
            $total_posts = absint($count_query->found_posts);
        }

        $max_pages = ceil( $total_posts / absint($query->query_vars['posts_per_page']) );

        if ( $page > $max_pages && $total_posts > 0 ) {
            return new WP_Error( 'rest_invalid_page_number',
                __( 'The page number requested is larger than the number of pages available.', 'listar' ),
                ['status' => 400]
            );
        }

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                Place_Model::assign_data_list($post);
            }
        }

        return rest_ensure_response([
            'success' => TRUE,
            'data' => $posts,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'max_page' => $max_pages,
                'total' => $total_posts,
            ]
        ]);
    }

    /**
     * Save data
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function save($request) {
        try {
            $post = Listar::valid_post(absint($request['post_id']));
            Wishlist_Model::save($this->user->ID, $post->ID);

            // Return endpoint data
            return rest_ensure_response([
                'success' => TRUE,
                'message' => __('Saved successfully', 'listar')
            ]);
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(),  ['status' => 400] );
        }
    }

    /**
     * Remove  data
     * - Single
     * - Multiple
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function remove($request) {
        try {
            $post = Listar::valid_post(absint($request['post_id']));
            Wishlist_Model::remove($this->user->ID, $post->ID);

            // Return endpoint data
            return rest_ensure_response([
                'success' => TRUE,
                'message' => __('Removed successfully', 'listar')
            ]);
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400] );
        }
    }

    /**
     * Reset all data
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function reset($request) {
        Wishlist_Model::reset($this->user->ID);

        // Return endpoint data
        return rest_ensure_response([
            'success' => TRUE,
            'message' => __('Reset successfully', 'listar')
        ]);
    }
}
