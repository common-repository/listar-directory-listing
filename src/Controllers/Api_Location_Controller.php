<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Location_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Api_Location_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    public function __construct() {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'location';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/location/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => '__return_true',
                'args'     => [
                    'parent_id' => [
                        'description'       => __( 'Parent ID.', 'listar' ),
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get list category
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        $terms = get_terms( Listar::$post_type.'_location', [
            'parent' => $request['parent_id'] ? absint($request['parent_id']) : 0,
            'hide_empty' => 0
        ]);

        if(is_array($terms) && !empty($terms)) {
            foreach($terms as &$term) {
                Location_Model::assign_metadata($term);
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
