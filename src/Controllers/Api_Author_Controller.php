<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Libraries\Post_Filtering;
use ListarWP\Plugin\Libraries\Comment_Filtering;
use ListarWP\Plugin\Libraries\Booking_Filtering;
use ListarWP\Plugin\Models\User_Model;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Comment_Model;
use ListarWP\Plugin\Models\Booking_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;
use WP_Error;

class Api_Author_Controller extends WP_REST_Controller
    implements Api_Interface_Controller
{

    /**
     * User viewing
     * - If no set user view the get user logged
     * @var WP_User|Object
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    protected $user;

    /**
     * Check user logged in is admin
     * @var bool
     */
    protected $is_admin = FALSE;

    public function __construct() {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'author';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, 'author/listing', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'listing' ],
                'permission_callback' => [ $this, 'permission_callback' ],
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

        register_rest_route( $this->namespace, 'author/booking', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'booking' ],
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

        register_rest_route( $this->namespace, 'author/overview', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'overview' ],
                'permission_callback' => [ $this, 'permission_callback' ],
                'args'     => [
                    'user_id' => [
                        'description'       => __( 'Current author.', 'listar' ),
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                        'minimum'           => 1,
                    ]
                ]
            ]
        ]);

        register_rest_route( $this->namespace, 'author/comments', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'comments' ],
                'permission_callback' => [ $this, 'permission_callback' ],
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
    }

    /**
     * Check token permission
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     * @since 1.0.8
     */
    public function permission_callback($request) {
        /**
         * User
         * - Request to view
         */
        if(isset($request['user_id'])) {
            $request_user_id = absint($request['user_id']);
            $this->user = get_userdata($request_user_id);
            if ( !$this->user ) {
                return new WP_Error( 'rest_permission', __( 'Author profile was not found.', 'listar' ), [ 'status' => 200 ] ); 
            }
        } else {
            /**
             * Self
             * - Check authorized header token
             */
            $current_user_id = get_current_user_id();
            if ( empty( $current_user_id ) ) {
                return new WP_Error( 'rest_permission', __( 'Listing data was not found (UNAUTHORIZED).', 'listar' ),[ 'status' => 200 ] );
            } else {
                $user = wp_get_current_user();
                $this->user = $user->data;
                $this->is_admin = listar_is_admin_user();
                if(!$this->user) {
                    return new WP_Error( 'rest_permission', __( 'Permission define (UNAUTHORIZED).', 'listar' ), [ 'status' => 200 ] );
                }
            }        
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
     * @since 1.0.8
     */
    public function listing($request)
    {
        // Set filtering by user
        $request->set_param('user_id', $this->user->ID);

        // Filtering
        $filtering = new Post_Filtering($request);
        $posts = $filtering->get_result();

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                Place_Model::assign_data_list($post);
            }
        }

        // User data
        $user_data = User_Model::refactor_user_data($this->user);
        $user_data['total_comment'] = Comment_Model::get_total_feedback($this->user->ID);
        $user_data['rating_avg'] = Comment_Model::get_author_avg_feedback($this->user->ID);    
        $user_data['submit'] = Setting_Model::submit_listing_use();    

        return rest_ensure_response([
            'success' => TRUE,
            'user' => $user_data,
            'pagination' => $filtering->get_pagination(),
            'data' => $posts,
        ]);
    }

    /**
     * List booking
     * - List booking of mine if I am a owner
     * - Waiting for aprove  
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.20
     */
    public function booking($request)
    {
        // Check authorized
        $current_user_id = get_current_user_id();
        $is_admin = FALSE;

        if ( empty( $current_user_id ) ) {
            return new WP_Error( 'rest_permission', __( 'Permission define (UNAUTHORIZED).', 'listar' ),[ 'status' => 200 ] );
        } else {
            $user = wp_get_current_user();
            $authorized = $user->data;
            $is_admin = listar_is_admin_user();
        }  

        // Filtering
        $filtering = new Booking_Filtering($request);
        if (!$is_admin) {
            $filtering->set_author_id($authorized->ID);
        }
        $data = $filtering->get_result();

        $result = [];
        $listar_model = new Booking_Model();

        if (!empty($data)) {
            foreach ($data as $item) {
                $listar_model->assign_meta_data($item->ID, (array)$item);
                $result[] = $listar_model->get_item_list();
            }
        }

        // Make sort status
        $status = [];
        foreach (listar_booking_status() as $key => $item) {
            if ($key == 'publish') {
                continue;
            }
            $status[] = [
                'title' => $item['title'],
                'field' => 'post_status',
                'lang_key' => 'post_status_' . strtolower($item['title']),
                'value' => $key
            ];
        }

        return rest_ensure_response([
            'success' => TRUE,
            'pagination' => $filtering->get_pagination(),
            'attr' => [
                'admin' => $is_admin,
                'sort' => [
                    [
                        'title' => __('Title Desc', 'listar'),
                        'field' => 'post_title',
                        'lang_key' => 'post_title_desc',
                        'value' => 'DESC'
                    ],
                    [
                        'title' => __('Title Asc', 'listar'),
                        'field' => 'post_title',
                        'lang_key' => 'post_title_asc',
                        'value' => 'ASC'
                    ],
                    [
                        'title' => __('Status', 'listar'),
                        'field' => 'comment_count',
                        'lang_key' => 'comment_count_desc',
                        'value' => 'DESC'
                    ]
                ],
                'status' => $status,
            ],
            'data' => $result
        ]);
    }

    /**
     * Get overview author
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.20
     */
    public function overview()
    {
        // User data
        $user_data = User_Model::refactor_user_data($this->user);
        $user_data['total_comment'] = Comment_Model::get_total_feedback($this->user->ID);
        $user_data['rating_avg'] = Comment_Model::get_author_avg_feedback($this->user->ID);
        $user_data['total_post'] = Post_Filtering::author_count($this->user->ID);

        return rest_ensure_response([
            'success' => TRUE,
            'data' => $user_data,
        ]);
    }

    /**
     * Get list category
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.8
     */
    public function comments($request)
    {
        // Set filtering comment by author's post
        $request->set_param('post_author', $this->user->ID);

        // Filtering
        $filtering = new Comment_Filtering($request);
        $result = $filtering->get_result();

        // Get posts & assign title
        $posts_index = [];
        $posts = get_posts([
            'post_type' => Listar::$post_type,
            'post__in' => array_column($result, 'comment_post_ID')
        ]);
        if(!empty($posts)) {
            foreach($posts as $post) {
                $posts_index[$post->ID] = $post->post_title;
            }
        }

        // Assign more data
        if(is_array($result) && !empty($result)) {
            foreach($result as &$comment) {
                // Meta data
                Comment_Model::assign_data_list($comment);
                // Post title
                if(isset($posts_index[$comment->comment_post_ID])) {
                    $comment->post_title = $posts_index[$comment->comment_post_ID];
                } else {
                    $comment->post_title = '';
                }
            }
        }

        return rest_ensure_response([
            'success' => TRUE,
            'pagination' => $filtering->get_pagination(),
            'data' => $result,
        ]);
    }
}
