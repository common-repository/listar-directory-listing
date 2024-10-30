<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Libraries\Post_Filtering;
use ListarWP\Plugin\Libraries\Term_Filtering;
use ListarWP\Plugin\Libraries\Notify;
use ListarWP\Plugin\Models\Location_Model;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;
use WP_Error;
use Exception;

class Api_Place_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    /**
     * Current user logged in
     * @var WP_User|Object
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    protected $user;

    public function __construct() {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'place';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/place/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => '__return_true',
                'args'     => [
                    'page' => [
                        'description'       => __( 'Current page of the listing.', 'listar' ),
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

        register_rest_route( $this->namespace, '/place/view', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'view' ],
                'permission_callback' => '__return_true',
                'args'     => [
                    'id' => [
                        'description'       => __( 'ID.', 'listar' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1,
                    ]
                ]
            ]
        ]);

        register_rest_route( $this->namespace, '/place/form', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'form' ],
                'permission_callback' => '__return_true'
            ]
        ]);

        register_rest_route( $this->namespace, '/place/save', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'save' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);

        register_rest_route( $this->namespace, '/place/delete', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'delete' ],
                'permission_callback' => [ $this, 'permission_callback' ],
                'args'     => [
                    'id' => [
                        'description'       => __( 'ID.', 'listar' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1,
                    ]
                ]
            ]
        ]);

        // List terms
        register_rest_route( $this->namespace, '/place/terms', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'terms' ],
                'permission_callback' => '__return_true',
                'args'     => [
                    'page' => [
                        'description'       => __( 'Current page of the term.', 'listar' ),
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
    }

    /**
     * Check token permission
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     * @since 1.0.11
     */
    public function permission_callback($request) {
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
     * Get list
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        $filtering = new Post_Filtering($request);
        $posts = $filtering->get_result();

        // Set user props
        $user = wp_get_current_user();
        if(!empty((array)$user->data)) {
            Place_Model::$user = $user->data;
        }

        // Set more props
        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                Place_Model::assign_data_list($post);
            }
        }

        return rest_ensure_response([
            'success' => TRUE,
            'pagination' => $filtering->get_pagination(),
            'data' => $posts
        ]);
    }

    /**
     * Get detail information of location
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public function view($request) {
        try {
            $post = Listar::valid_post($request['id']);            

            // Set user props
            $user = wp_get_current_user();
            if(!empty((array)$user->data)) {
                Place_Model::$user = $user->data;
            }

            // Set view props
            Place_Model::assign_data_view($post);

            if(isset($request['latitude']) && $request['latitude'] != ''
                && isset($request['longitude']) && $request['longitude'] != '') {
                $post->distance = listar_gps_distance($request['latitude'], $request['longitude'],
                    get_post_meta($post->ID, 'latitude', true), get_post_meta($post->ID, 'longitude', true));
            } else {
                $post->distance = '';
            }

            /**
             * Set claim props
             * - No body claim
             * - Setting is enable
             */
            $post->claim_use = !Place_Model::is_claimed($post->ID) && Setting_Model::get_option('claim_listing_use');
            $post->claim_verified = Place_Model::is_claimed($post->ID) && Setting_Model::get_option('claim_badget');

            // Return endpoint data
            return rest_ensure_response([
                'success' => TRUE,
                'data' => $post
            ]);
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400 ] );
        }
    }

    /**
     * Init form data for create post
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     * @throws Exception
     * @since 1.0.8
     */
    public function form($request)
    {
        try {
            $states = $cities = [];
            if(isset($request['post_id'])) {
                $post = Listar::valid_post($request['post_id']);
                Place_Model::assign_address_data($post);

                if(isset($post->location['country']) && isset($post->location['country']['id'])) {
                    $states = Location_Model::get_locations($post->location['country']['id']);
                }

                if(isset($post->location['state']) && isset($post->location['state']['id'])) {
                    $cities =  Location_Model::get_locations($post->location['state']['id']);
                }
            }

            // Return endpoint data
            return rest_ensure_response([
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'map_use' => Setting_Model::get_option('map_use'),
                'map_center' => [(float)Setting_Model::get_option('gmap_center_lat'), (float)Setting_Model::get_option('gmap_center_long')],
                'map_zoom' => (int) Setting_Model::get_option('gmap_zoom'),
                'countries'  => get_terms( Listar::$post_type.'_location', [
                    'parent' => 0,
                    'hide_empty' => 0
                ]),
                'states' => $states,
                'cities' => $cities,
                'categories' => get_terms(Listar::$post_type.'_category', [
                    'hide_empty' => 0
                ]),
                'features' => get_terms( Listar::$post_type.'_feature', [
                    'parent' => 0,
                    'hide_empty' => 0
                ]),
                'range_time' => listar_get_range_time(0, 24, 2),
                'day_of_weeks' => listar_get_open_hours_label(),
            ]);
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400 ] );
        }
    }

    /**
     * Save the post data
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     */
    public function save($request)
    {
        try {
            $post_id = $request->get_param('post_id');

            // Check permission & option
            if(!listar_is_admin_user() && !Setting_Model::submit_listing_use()) {
                throw new Exception(__('Submit listing feature is disabled'));
            }

            // Common data
            $data = [
                'post_type' => Listar::$post_type,
                'post_title' => wp_strip_all_tags($request->get_param('title')),
                'post_content' => $request->get_param('content'),
                'post_excerpt' => $request->get_param('content'),
                'post_status' => Setting_Model::submit_listing_approval_use() ? 'pending' : 'publish',
            ];

            // Tag
            $tags_input = $request->get_param('tags_input');
            if($tags_input) {
                $data['tags_input'] = esc_attr($tags_input);
            }

            // Taxonomy
            $tax_input = $request->get_param('tax_input');
            if(is_array($tax_input) && !empty($tax_input)) {
                $data['tax_input'] = $request->get_param('tax_input');
            }

            // Update or Insert
            if($post_id) {
                // Valid post data
                Listar::valid_post($post_id);
                // Update data
                $data['ID'] = $post_id;
                wp_update_post($data);
            } else {
                $post_id = wp_insert_post($data, TRUE);
                // Hook
                do_action( 'submit_listar_claim', $post_id, $data);
            }

            // Set post thumb for post
            $thumbnail = $request->get_param('thumbnail') ;
            if($thumbnail) {
                set_post_thumbnail($post_id, $thumbnail);
            }

            // Update meta data
            Place_Model::set_metadata($post_id, $request->get_body_params());            

            // Return endpoint data
            return rest_ensure_response([
                'success' => TRUE,
                'post_id' => $post_id
            ]);
        } catch (Exception $e) {
            error_log('listing.save.error:'.$e->getMessage());
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400 ] );
        }
    }

    /**
     * Delete post
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     */
    public function delete($request)
    {
        try {
            $post_id = $request->get_param('post_id');

            $post = Listar::valid_post($post_id);

            // Check author delete
            if($post->post_author !== $this->user->ID) {
                return new WP_Error( 'rest_permission', __( 'You are not author so can not remove', 'listar' ));
            }

            // Delete data include meta data
            wp_delete_post($post_id);

            // Return endpoint data
            return rest_ensure_response([
                'success' => TRUE,
                'post_id' => $post_id
            ]);
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400 ] );
        }
    }

    /**
     * Get list terms
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.7
     */
    public function terms($request) {
        $filtering = new Term_Filtering($request);
        $data = $filtering->get_result();
        $result = [];

        if(!empty($data)) {
            foreach($data as $item) {
                $result[] = [
                    'id' => $item->term_id,
                    'name' => $item->name,
                    'slug' => $item->slug
                ];
            }
        }

        return rest_ensure_response([
            'success' => TRUE,
            'pagination' => $filtering->get_pagination(),
            'data' => $result
        ]);
    }
}
