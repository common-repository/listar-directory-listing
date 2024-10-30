<?php
namespace ListarWP\Plugin\Controllers;
use WP_REST_Controller;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Category_Model;
use WP_REST_Server;
use WP_REST_Response;
use WP_REST_Request;
use WP_Query;

class Api_Category_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    public function __construct() {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'category';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/'.$this->rest_base.'/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => '__return_true'
            ]
        ]);

        register_rest_route( $this->namespace, '/'.$this->rest_base.'/list_discover', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list_discover' ],
                'permission_callback' => '__return_true'
            ]
        ]);
    }

    /**
     * Get list category
     *
     * @param WP_REST_Request $request Full data about  the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {

        if(isset($request['category_id'])) {
            $query = [
                'parent' => absint($request['category_id']),
                'hide_empty' => 0
            ];
        } else {
            $query = [
                'parent' => 0,
                'hide_empty' => 0,
            ];
        }

        $result = [];
        $terms = get_terms( Listar::$post_type.'_category', $query);

        if(is_array($terms) && !empty($terms)) {
            foreach($terms as &$term) {
                Category_Model::assign_metadata($term);
            }
        }
        $result = $terms;

        
        return rest_ensure_response([
            'success' => TRUE,
            'size' => sizeof($result),
            'data' => $result
        ]);
    }

    /**
     * Get list category and related location
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.10
     */
    public function list_discover($request) {
        $terms = get_terms( Listar::$post_type.'_category', [
            'parent' => 0,
            'hide_empty' => 0
        ]);

        if(is_array($terms) && !empty($terms)) {
            foreach($terms as &$term) {
                Category_Model::assign_metadata($term);
                $term->posts = [];
                // Get related data
                $args = [
                    'post_type' => Listar::$post_type,
                    'post_status'  => 'publish',
                    'posts_per_page' => 10,
                    'tax_query' => [
                        [
                            'taxonomy' => Listar::$post_type.'_category',
                            'field'    => 'term_id',
                            'terms'    => absint($term->term_id)
                        ]
                    ]
                ];

                $query = new WP_Query($args);
                $posts = $query->get_posts();

                if(!empty($posts)) {
                    foreach ($posts as $post) {
                        Place_Model::assign_image($post);
                        $term->posts[] = [
                            'ID' => $post->ID,
                            'post_title' => $post->post_title,
                            'image' => $post->image,
                        ];
                    }
                }
            }
        }

        return rest_ensure_response([
            'success' => TRUE,
            'code' => '',
            'message' => '',
            'data' => $terms
        ]);
    }
}
