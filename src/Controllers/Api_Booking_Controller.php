<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use ListarWP\Plugin\Libraries\Cart;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Libraries\Booking;
use ListarWP\Plugin\Libraries\Booking_Filtering;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Booking_Model;
use WP_REST_Controller;
use WP_User;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Exception;
use Throwable;

class Api_Booking_Controller extends WP_REST_Controller
    implements Api_Interface_Controller
{

    /**
     * Current user logged in (WP_User->data)
     * @var WP_User|Object
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.11
     */
    protected $user;

    /**
     * Check user logged in is admin
     * @var bool
     * @since 1.0.23
     */
    protected $is_admin = FALSE;

    public function __construct()
    {
        $this->namespace = 'listar/v1';
        $this->rest_base = 'booking';
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

        register_rest_route($this->namespace, '/' . $this->rest_base . '/order', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'order'],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/cart', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'cart'],
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

        register_rest_route($this->namespace, '/' . $this->rest_base . '/cancel', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'cancel' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/return', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'return' ],
                'permission_callback' => '__return_true',
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
     * @since 1.0.8
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
     * Get detail information of location
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.11
     */
    public function form($request) {
        if(isset($request['resource_id'])) {
            try {
                $resource = Listar::valid_post($request['resource_id']);
                $booking_style = get_post_meta($resource->ID, 'booking_style', TRUE);
                $price = Setting_Model::currency_format(get_post_meta($resource->ID, 'booking_price', TRUE));

                $booking = new Booking();
                $booking->set_booking_style($booking_style);

                $bank_account_list = Setting_Model::get_option('bank_account_list');

                return rest_ensure_response([
                    'success' => TRUE,
                    'data' => [
                        'id' => (int)$request['resource_id'],
                        'type' => $booking_style,
                        'price' => $price,
                        'start_date' => date('Y-m-d'),
                        'end_date' => date('Y-m-d'),
                        'select_options' => $booking->booking_style->select_options(),
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
                return new WP_Error('rest_invalid_post', $e->getMessage(), ['status' => 200]);
            }
        } else {
            return NULL;
        }
    }

    /**
     * Get list
     *
     * @param WP_REST_Request $request Full data about  the request.
     * @return WP_REST_Response
     *
     * @since 1.0.11
     */
    public function order($request) {
        try {
            //=== Booking Type
            $resource_id = absint($request->get_param('resource_id'));
            $booking_style = $request->get_param('booking_style');
            $payment_method = $request->get_param('payment_method');

            $booking = new Booking($booking_style, $resource_id);
            $booking->set_created_via('mobile');
            $booking->booking_style->initialize([
                'person' => absint($request->get_param('person')),
                'adult' => absint($request->get_param('adult')),
                'children' => absint($request->get_param('children')),
                'start_date' => $request->get_param('start_date'),
                'end_date' => $request->get_param('end_date'),
                'start_time' => $request->get_param('start_time'),
                'end_time' => $request->get_param('end_time'),
                'memo' => trim(sanitize_textarea_field($request->get_param('memo'))),
                'table_num' => absint($request->get_param('table_num'))
            ]);

            //=== Customer
            $customer = new Customer($this->user->ID);
            $customer->initialize([
                'first_name' => sanitize_text_field($request->get_param('first_name')),
                'last_name' => sanitize_text_field($request->get_param('last_name')),
                'email' => sanitize_email($request->get_param('email')),
                'phone' => sanitize_text_field($request->get_param('phone')),
                'address' => sanitize_text_field($request->get_param('address'))
            ]);

            //=== Cart
            // - Just support booking one item
            $cart = new Cart();
            $cart->set_data([
                [
                    'id'      => $booking->resource->get_id(),
                    'price'   => $booking->resource->get_price(),
                    'name'    => $booking->resource->get_name(),
                    'qty'     => $booking->booking_style->qty(),
                    'options' => $booking->booking_style->options(),
                ],
            ]);

            $booking->set_cart($cart);
            $booking->set_customer($customer);

            //=== Payment Method (optional)
            $booking->set_payment_method($payment_method);

            //=== Confirm Booking
            $booking->create_order();

            //=== Booking detail
            $booking_model = new Booking_Model();
            $data = $booking_model->get_item_view($booking->order_id);

            return rest_ensure_response([
                'success' => TRUE,
                'msg' => '',
                'data' => $data,
                'payment' => $booking->payment->result,
            ]);
        } catch (Exception $e) {
            error_log("booking.order.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Estimate booking data
     *
     * @param WP_REST_Request $request Full data about  the request.
     * @return WP_REST_Response
     *
     * @since 1.0.11
     */
    public function cart($request) {
        try {
            //=== Booking Type
            $resource_id = absint($request->get_param('resource_id'));
            $booking_style = $request->get_param('booking_style');

            $booking = new Booking($booking_style, $resource_id);
            $booking->booking_style->initialize([
                'person' => absint($request->get_param('person')),
                'adult' => absint($request->get_param('adult')),
                'children' => absint($request->get_param('children')),
                'start_date' => $request->get_param('start_date'),
                'end_date' => $request->get_param('end_date'),
                'start_time' => $request->get_param('start_time'),
                'end_time' => $request->get_param('end_time'),
                'table_num' => absint($request->get_param('table_num'))
            ]);

            $booking->booking_style->validate();

            //=== Cart
            $cart = new Cart();
            $cart->set_data([
                [
                    'id'      => $booking->resource->get_id(),
                    'price'   => $booking->resource->get_price(),
                    'name'    => $booking->resource->get_name(),
                    'qty'     => $booking->booking_style->qty(),
                    'options' => $booking->booking_style->options(),
                ],
            ]);

            return rest_ensure_response([
                'success' => TRUE,
                'msg' => '',
                'attr' => [
                    'total' => $cart->total(),
                    'currency' => $booking->currency,
                    'total_display' =>  Setting_Model::currency_format($cart->total(), $booking->currency),
                    'total_items' => $cart->total_items(),
                ],
                'data' => $cart->contents(),
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        } catch (Throwable $e) {
            error_log("booking.cart.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Completed Payment
     * - Support PayPal only
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.11
     */
    public function return($request) {
        try {
            $token = $request->get_param('token');
            $payer_id = $request->get_param('PayerID');

            if($token != '') {
                $booking = new Booking();
                $booking->complete_payment($token);
            } else {
                throw new Exception(__('Undefined token'));
            }

            // Return booking detail
            $booking_model = new Booking_Model();
            $data = $booking_model->get_item_view($booking->payment->order_id);

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


    /**
     * Cancel Payment
     * - Support PayPal only
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.11
     */
    public function cancel($request) {
        try {
            $token = $request->get_param('token');
            $payer_id = $request->get_param('PayerID');

            if($token !== '') {
                $booking = new Booking();
                $booking->cancel_payment($token);
            } else {
                throw new Exception(__('Undefined token'));
            }

            // Return booking detail
            $booking_model = new Booking_Model();
            $data = $booking_model->get_item_view($booking->payment->order_id);

            return rest_ensure_response([
                'success' => true,
                'data' => $data,
                'msg' => __('Transaction is canceled with ID '.$booking->payment->transaction_id)
            ]);
        } catch (Exception $e) {
            error_log("booking.cancel.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel booking by id
     * - Support PayPal only
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.11
     */
    public function cancel_by_id($request) {
        try {
            $booking_id = $request->get_param('id');

            // Booking owner validation
            $booking_model = new Booking_Model();
            $booking_model->item($booking_id);
            if($this->user->ID != $booking_model->get_customer_user()) {
                throw new Exception(__('You are not not booking owner so can not cancel this booking'));
            }

            if($booking_model->get_status() == 'canceled') {
                throw new Exception(__('This booking is already canceled'));
            }

            // Cancel booking
            if($booking_id > 0) {
                $booking = new Booking();
                $booking->cancel($booking_id);
            } else {
                throw new Exception(__('Undefined token'));
            }

            return rest_ensure_response([
                'success' => true,
                'data' => $booking_model->get_item_view($booking_id), // Return booking detail
                'msg' => __('Canceled booking ID '.$booking_id)
            ]);
        } catch (Exception $e) {
            error_log("booking.cancel_by_id.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Accept booking by id
     * - If the payment is processing then can't process
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @version 1.0.23
     */
    public function accept_by_id($request) {
        try {
            $booking_id = $request->get_param('id');

            // Booking validation
            $booking_model = new Booking_Model();
            $booking_model->item($booking_id);

            if(!$this->is_admin) {
                throw new Exception(__('This booking only process by administrator'));
            }

            // Accept booking
            $booking = new Booking();
            $booking->accept($booking_id);

            return rest_ensure_response([
                'success' => true,
                'data' => $booking_model->get_item_view($booking_id), // Return booking detail
                'msg' => __('Accepted booking ID '.$booking_id)
            ]);
        } catch (Exception $e) {
            error_log("booking.accept_by_id.failed: ".$e->getMessage());
            return rest_ensure_response([
                'success' => false,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * List booking
     * - My request to book
     * 
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.11
     */
    public function list($request)
    {
        // Filtering
        $filtering = new Booking_Filtering($request);
        $filtering->set_user_id($this->user->ID); // My booking
        $data = $filtering->get_result();

        $result = [];
        $listar_model = new Booking_Model();

        if(!empty($data)) {
            foreach ($data as $item) {
                $listar_model->assign_meta_data($item->ID, (array)$item);
                $result[] = $listar_model->get_item_list();
            }
        }

        // Make sort status
        $status = [];
        foreach(listar_booking_status() as $key => $item) {
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
            'data' => $result
        ]);
    }

    /**
     * Get detail information of location
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.11
     */
    public function view($request) {
        try {
            $booking_model = new Booking_Model();
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
}
