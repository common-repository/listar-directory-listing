<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Libraries\Claim_Filtering;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Libraries\Cart;
use ListarWP\Plugin\Libraries\Claim;
use ListarWP\Plugin\Models\Claim_Model;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_REST_Controller;
use WP_User;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Exception;

class Api_Claim_Controller extends WP_REST_Controller
    implements Api_Interface_Controller
{

    /**
     * Current user logged in (WP_User->data)
     * @var WP_User|Object
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.30
     */
    protected $user;

    /**
     * Check user logged in is admin
     * @var bool
     * @since 1.0.30
     */
    protected $is_admin = FALSE;

    public function __construct()
    {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'claim';
    } 

    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/form', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'form' ],
                'permission_callback' => '__return_true',
                'args'     => [
                    'resource_id' => [
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

        register_rest_route($this->namespace, '/' . $this->rest_base . '/submit', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'submit'],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);
       
        register_rest_route($this->namespace, '/' . $this->rest_base . '/pay', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'pay'],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/return', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'return' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/cancel', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'cancel' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/cancel_by_id', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'cancel_by_id'],
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

        register_rest_route($this->namespace, '/' . $this->rest_base . '/accept_by_id', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'accept_by_id'],
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


        register_rest_route($this->namespace, '/' . $this->rest_base . '/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
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

        register_rest_route($this->namespace, '/' . $this->rest_base . '/view', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'view' ],
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
    }

    /**
     * Check token permission
     * @param WP_REST_Request $request
     * @return bool|WP_Error|WP_REST_Response
     * @since 1.0.30
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

        $this->is_admin = listar_is_admin_user();

        return TRUE;
    }

    /**
     * Get list data
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.30
     */
    public function list($request)
    {
        // Filtering
        $filtering = new Claim_Filtering($request);
        $filtering->set_user_id($this->user->ID); 
        $data = $filtering->get_result();

        $result = [];
        $model = new Claim_Model();

        if(!empty($data)) {
            foreach ($data as $item) {
                $model->assign_meta_data($item->ID, (array)$item);
                $result[] = $model->get_item_list();
            }
        }

        // Make sort status
        $status = [];
        foreach(listar_claim_status() as $key => $item) {
            if($key == 'publish') {
                continue;
            }
            $status[] = [
                'title' => $item['title'],
                'field' => 'post_status',
                'lang_key' => 'post_status_'.strtolower($item['title']),
                'value' => $key
            ];
        }

        return rest_ensure_response([
            'success' => TRUE,
            'data' => $result,
            'pagination' => $filtering->get_pagination(),
            'attr' => [
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
        ]);
    }

    /**
     * Get detail
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.30
     */
    public function view($request) {
        try {
            $booking_model = new Claim_Model();
            $data = $booking_model->get_item_view($request['id']);

            return rest_ensure_response([
                'success' => TRUE,
                'data' => $data
            ]);

        } catch (Exception $e) {
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Loading init form
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.30
     */
    public function form($request) {
        if(isset($request['id'])) {
            try {
                $id = absint($request['id']);
                $listing = Listar::valid_post($id);
                // Set view props
                Place_Model::assign_address_data($listing);
                    
                // Bank accounts for payment
                $bank_account_list = Setting_Model::get_option('bank_account_list');

                // Return endpoint data
                return rest_ensure_response([
                    'success' => TRUE,
                    'data' => [
                        'title' => $listing->post_title,
                        'address' => $listing->full_address,
                        'button_text' => Setting_Model::get_option('claim_button_text'),
                    ],
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
        } else {
            return NULL;
        }
    }

    /**
     * Submit listing 
     *
     * @param WP_REST_Request $request Full data about  the request.
     * @return WP_REST_Response
     *
     * @since 1.0.30
     */
    public function submit($request) 
    {
        try {
            $id = absint($request->get_param('id'));
            $memo = sanitize_text_field($request->get_param('memo'));

            $claim = new Claim();
            $claim->set_created_via('mobile');
            $claim->set_listing_id($id);
            $claim->set_memo($memo);

            //=== Customer
            $customer = new Customer($this->user->ID);
            $customer->initialize([
                'first_name' => sanitize_text_field($request->get_param('first_name')),
                'last_name' => sanitize_text_field($request->get_param('last_name')),
                'email' => sanitize_email($request->get_param('email')),
                'phone' => sanitize_text_field($request->get_param('phone'))
            ]);
            $claim->set_customer($customer);

            //=== Request
            $claim->verify();
            $claim_id = $claim->request();

            return rest_ensure_response([
                'success' => TRUE,
                'msg' => '',
                'id' => $claim_id
            ]);
        } catch (Exception $e) {
            error_log("claim.submit.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Accept request to claim by id
     * - Set the claim price
     * - Set the claim rule
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.31
     */
    public function accept_by_id($request) {
        try {
            $id = $request->get_param('id');

            // Validation
            $claim_model = new Claim_Model();
            $claim_item = $claim_model->item($id);

            if(!$this->is_admin) {
                throw new Exception(__('This claim request only process by the administrator', 'listar'));
            }

            if($claim_item->get_status() !== 'pending') {
                throw new Exception(__('Can not process request. The status must be pending.', 'listar'));
            }

            // Accept
            $claim = new Claim();
            $data = $claim->accept($claim_item);

            return rest_ensure_response([
                'success' => true,
                'data' => $data,
                'msg' => __('Accepted claim request ID '.$id)
            ]);
        } catch (Exception $e) {
            error_log("claim.accept_by_id.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel Payment
     * > It's cancellation when user is processing the payment
     * > Payment 
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.31
     */
    public function cancel($request) {
        try {
            $token = $request->get_param('token');
            $payer_id = $request->get_param('PayerID');

            if(!empty($token)) {
                $claim = new Claim();
                $claim->cancel_payment($token);
            } else {
                throw new Exception(__('Undefined token'));
            }

            // Return detail
            $model = new Claim_Model();
            $data = $model->get_item_view($claim->payment->order_id);

            return rest_ensure_response([
                'success' => true,
                'data' => $data,
                'msg' => __('Transaction is canceled with ID '.$claim->payment->transaction_id)
            ]);
        } catch (Exception $e) {
            error_log("claim.cancel.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel By Id
     * > It's force cancellation without processing payment
     * > Non Payment
     * 
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.31
     */
    public function cancel_by_id($request) {
        try {
            $id = $request->get_param('id');

            // owner validation
            $model = new Claim_Model();
            $model->item($id);
            if($this->user->ID != $model->get_customer_user()) {
                throw new Exception(__('You are not not claim owner so can not cancel this claim request'));
            }

            if($model->get_status() == 'canceled') {
                throw new Exception(__('This claim request is already canceled'));
            }

            // Cancel
            if($id > 0) {
                $claim = new Claim();
                $claim->cancel($id);
            } else {
                throw new Exception(__('Undefined token'));
            }

            return rest_ensure_response([
                'success' => true,
                'data' => $model->get_item_view($id), // Return detail
                'msg' => __('Canceled claim request ID '.$id)
            ]);
        } catch (Exception $e) {
            error_log("claim.cancel_by_id.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Pay
     * - It's just call to payment getway & return transaction for process next step 
     * 
     * @param WP_REST_Request $request Full data about  the request.
     * @return WP_REST_Response
     *
     * @since 1.0.31
     */
    public function pay($request) 
    {
        try {
            $id = absint($request->get_param('id')); // Claim Id
            $payment_method = $request->get_param('payment_method');

            $model = new Claim_Model();
            $claim_item = $model->item($id);

            $claim = new Claim();
            $claim->set_created_via('mobile');
            $claim->set_resource($claim_item);

            //=== Customer
            $customer = new Customer($this->user->ID);
            $customer->initialize([
                'first_name' => $claim_item->get_value('_billing_first_name'),
                'last_name' => $claim_item->get_value('_billing_last_name'),
                'email' => $claim_item->get_value('_billing_email'),
                'phone' => $claim_item->get_value('_billing_phone')
            ]);
            $claim->set_customer($customer);

            //=== Cart
            $cart = new Cart();
            $cart->set_data([
                [
                    'id'      => $claim->resource->get_id(),
                    'price'   => $claim->resource->get_price(),
                    'name'    => $claim->resource->get_name(),
                    'qty'     => 1,
                    'options' => []
                ],
            ]);
            $claim->set_cart($cart);

            //=== Payment Method (optional)
            $claim->set_payment_method($payment_method);

            //=== Pay
            $claim->order_id = $id;
            $claim->create_order();

            //=== Data 
            $data = $model->get_item_view($claim->order_id);

            return rest_ensure_response([
                'success' => TRUE,
                'msg' => '',
                'data' => $data,
                'payment' => $claim->payment->result,
            ]);
        } catch (Exception $e) {
            error_log("claim.submit.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Completed Payment
     * 
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.31
     */
    public function return($request) {
        try {
            $token = $request->get_param('token');
            $payer_id = $request->get_param('PayerID');
            
            if(!empty($token)) {
                $claim = new Claim();
                $claim->complete_payment($token);
            } else {
                throw new Exception(__('Undefined token'));
            }

            // Return booking detail
            $claim_model = new Claim_Model();
            $data = $claim_model->get_item_view($claim->payment->order_id);

            return rest_ensure_response([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("booking.return.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'code' => $e->getCode(),
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }
}
