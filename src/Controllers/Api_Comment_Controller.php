<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Models\Comment_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Comment_Query;
use Exception;
use ListarWP\Plugin\Models\Booking_Model;
use ListarWP\Plugin\Models\Claim_Model;


class Api_Comment_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    /**
     * Current user logged in (WP_User->data)
     * @var WP_User|Object
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.33
     */
    protected $user;

    public function __construct() {
        $this->namespace = 'listar/v1';

        // Comment handler
        // Register > callback function for action wp_insert_comment
        $comment_obj = new Comment_Model();
        add_action('wp_insert_comment', [$comment_obj, 'after_save_comment']);
    }

    /**
     * Register Rest API router
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/comments', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);
    }

    /**
     * Check token permission
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     * @since 1.0.8
     */
    public function permission_callback($request) {
        $current_user_id = get_current_user_id();
        if ( !empty( $current_user_id ) ) {
             // Current user logged in
            $user = wp_get_current_user();
            $this->user = $user->data;
        }
       
        return TRUE;
    }

    /**
     * Get list category
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        try {
            $post_id = absint($request['post_id']);
            if ( $post_id <= 0 ) {
                throw new Exception(__( 'Invalid ID.', 'listar'));
            }

            $post = get_post($post_id);

            if ( empty( $post ) || empty( $post->ID )) {
                throw new Exception(__( 'Invalid data.', 'listar'));
            }

            $args = [
                'status' => 'approve',
                'post_id' => $post->ID
            ];

            $query  = new WP_Comment_Query($args);
            $rows   = $query->get_comments();

            if(is_array($rows) && !empty($rows)) {
                foreach($rows as &$comment) {
                    Comment_Model::assign_data_list($comment);
                }
            }

            /**
             * Allow the user give rate and comment
             * - user has claimed
             * - user has booked
             */
            $submit = true;
            if(Setting_Model::get_option('user_comment_validate') && !empty($this->user)) {
                if(!Claim_Model::has_claimed_data($this->user->ID, $post_id) && !Booking_Model::has_booked_data($this->user->ID, $post_id)) {
                    $submit = false;
                }
            }

            return rest_ensure_response([
                'success' => TRUE,
                'attr' => [
                    'rating' => Comment_Model::get_rating_meta($post->ID),
                    'submit' => $submit
                ],
                'data' => $rows
            ]);
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), [
                'status' => 400
            ]);
        }
    }
}
