<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Post_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;
use WP_Error;
use Exception;

class Api_Post_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    public function __construct() {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'post';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base.'/home', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'home' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        register_rest_route( $this->namespace, $this->rest_base.'/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => '__return_true',
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
                    ]
                ]
            ]
        ]);

        register_rest_route( $this->namespace, $this->rest_base.'/view', [
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
                        'minimum'           => 1
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get home data
     * - Stick post
     * - Recent posts
     * - Categories
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public function home($request) {

        // Sticky
        $p_sticky = get_option( 'sticky_posts' );
        $sticky = get_post( (int) $p_sticky[0]);
        if ( !empty( $sticky->ID)) {
            Post_Model::assign_data_view($sticky);
            $sticky = [
                'ID' => $sticky->ID,
                'post_date' => $sticky->post_date,
                'post_title' => $sticky->post_title,
                'post_author' => $sticky->post_author,
                'guid' => $sticky->guid,
                'author' => $sticky->author,
                'image' => $sticky->image,
                'categories' => $sticky->categories,
            ];
        }

        // Categories
        $categories = get_terms( 'category', [
            'parent' => 0,
            'hide_empty' => 0
        ]);

        $args = [];
        // Keyword
        if(isset($request['s']) && $request['s'] != '') {
            $args['s'] = sanitize_text_field($request['s']);
        }

        // Sort
        if(isset($request['orderby']) && $request['orderby'] != ''
            && isset($request['order']) && $request['order'] != '') {
            $args['orderby'] = sanitize_sql_orderby($request['orderby']);
            $args['order'] = sanitize_text_field($request['order']);
        }

        // Category
        if(isset($request['category']) && $request['category'] != '') {
            $args['tax_query'][] = [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => is_array($request['category']) ? $request['category'] : absint($request['category'])
            ];
        }

        // Recent
        $posts = Post_Model::get_recent_data($args);

        return rest_ensure_response([
            'success' => TRUE,
            'sticky' => $sticky,
            'categories' => $categories,
            'posts' => $posts
        ]);
    }

    /**
     * Get list category
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public function list($request) {
        $per_page = isset($request['per_page']) ? absint($request['per_page'])
            : absint(Setting_Model::get_option('per_page'));

        $args = [
            'ignore_sticky_posts' => 1,
            'post_type'     => Post_Model::$post_type,
            'post_status'   => 'publish',
            'paged'         =>  isset($request['page']) ? absint($request['page']) : 1,
            'posts_per_page' => $per_page
        ];

        // Keyword
        if(isset($request['s']) && $request['s'] != '') {
            $args['s'] = sanitize_text_field($request['s']);
        }

        // Sort
        if(isset($request['orderby']) && $request['orderby'] != ''
            && isset($request['order']) && $request['order'] != '') {
            $args['orderby'] = sanitize_sql_orderby($request['orderby']);
            $args['order'] = sanitize_text_field($request['order']);
        }

        // Category
        if(isset($request['category']) && $request['category'] != '') {
            $args['tax_query'][] = [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => is_array($request['category']) ? $request['category'] : absint($request['category'])
            ];
        }

        $query  = new WP_Query($args);
        $posts  = $query->get_posts();

        $paged       = absint($args['paged']);
        $total_posts = absint($query->found_posts);

        if ( $total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $args );
            $total_posts = (int) $count_query->found_posts;
        }

        $max_pages = ceil( $total_posts / (int) $query->query_vars['posts_per_page'] );

        if ( $paged > $max_pages && $total_posts > 0 ) {
            return new WP_Error( 'rest_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'listar' ), ['status' => 400] );
        }

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                unset($post->post_content);
                Post_Model::assign_data_list($post);
            }
        }

        $response = rest_ensure_response([
            'success' => TRUE,
            'sort' => [
                [
                    'title' => __('Lastest Post', 'listar'),
                    'field' => 'post_date',
                    'value' => 'DESC'
                ],
                [
                    'title' => __('Oldest Post', 'listar'),
                    'field' => 'post_date',
                    'value' => 'ASC'
                ]
            ],
            'pagination' => [
                'page' => $paged,
                'per_page' => $per_page,
                'max_page' => $max_pages,
                'total' => $total_posts,
            ],
            'data' => $posts
        ]);

        return $response;
    }

    /**
     * Get detail information of location
     *
     * @param int $id Location id
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.26
     */
    public function view($request) {
        try {
            $post_id = absint($request['id']);
            if ( $post_id <= 0 ) {
                throw new Exception(__( 'Invalid ID.', 'listar'));
            }

            $post = get_post( (int) $post_id);

            if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== Post_Model::$post_type) {
                throw new Exception(__( 'Invalid data.', 'listar'));
            }

            Post_Model::assign_data_view($post);

            // Return endpoint data
            $response = rest_ensure_response([
                'success' => TRUE,
                'data' => $post
            ]);

            return $response;

        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), array( 'status' => 400 ) );
        }
    }
}
