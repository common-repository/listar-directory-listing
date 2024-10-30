<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Models\User_Model;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\Notification_Model;
use ListarWP\Plugin\Libraries\Otp;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;
use WP_Error;
use Exception;
use Throwable;

class Api_Auth_Controller extends WP_REST_Controller
    implements Api_Interface_Controller {

    /**
     * @var WP_User|Object
     */
    protected $user;

    /**
     * Expire time
     * @var string
     */
    protected $exp = '';

    public function __construct() {
        $this->namespace = 'listar/v1';

        // Fire when insert/update user via rest API insert/update user
        add_action( 'rest_after_insert_user', [$this, 'rest_after_insert_user'], 10, 3 );

        // Fire when login failed
        add_action('wp_login_failed', [$this, 'auth_login_failed'], 10, 2);

        // Refactor user's data when user has authorized
        add_filter('jwt_auth_token_before_dispatch', [$this, 'auth_token_before_dispatch'], 10, 2);

        // Before sign 
        add_filter('jwt_auth_token_before_sign', [$this, 'auth_token_before_sign'], 10, 2);

        // Change auth expire date
        add_filter('jwt_auth_expire', [$this, 'auth_expire'], 10, 1);
    }

    public function register_routes() {
        // Reset Password
        register_rest_route( $this->namespace, '/auth/reset_password', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'reset_password' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        // Register
        register_rest_route( $this->namespace, '/auth/register', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'register' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        // Get User Profile
        register_rest_route( $this->namespace, '/auth/user', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'user' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        // Deactivate account
        register_rest_route( $this->namespace, '/auth/deactivate', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'deactivate' ],
                'permission_callback' => '__return_true',
            ]
        ]);

        // OTP request
        register_rest_route( $this->namespace, '/auth/otp', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'otp' ],
                'permission_callback' => '__return_true',
            ]
        ]);
    }

    /**
     * Reset user's password
     * - Send email + attach url reset password
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function reset_password($request) {
        $email = sanitize_email($request['email']);

        if(!is_email($email)) {
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => __('Email invalid', 'listar')
            ]);
        }

        $user_data = get_user_by('email',  $email);

        if ( !$user_data ) {
            return rest_ensure_response([
                'code' => 'auth_reset_password',
                'message' => __('User not found. Please correct your email again.', 'listar'),
                'data' => [
                    'status' => 403
                ]
            ]);
        }

        //=== OTP
        if (is_wp_error($error = $this->otp_validation($email))) {
            return $error;
        }

        //=== Email to reset password URL
        do_action('lostpassword_post');

        $headers = 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = get_password_reset_key( $user_data );

        if ( is_multisite() ) {
            $blogname = $GLOBALS['current_site']->site_name;
        } else {
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }

        // Customize notification reset password content
        if(Setting_Model::get_option('email_reset_password_use')) {
            $data = [
                'blogname' => $blogname,
                'site_title' => get_bloginfo(),
                'site_address' => get_site_url(),
                'user_login' => $user_data->user_login,
                'user_email' => $user_data->user_email,
                'user_nicename' => $user_data->user_nicename,
                'display_name' => $user_data->display_name,
            ];
            $title = listar_pattern_replace($data, Setting_Model::get_option('email_reset_password_subject'));
            $message = listar_pattern_replace($data, Setting_Model::get_option('email_reset_password_content'));      
            $message .= network_site_url('wp-login.php?action=rp&key='.$key.'&login=' . rawurlencode($user_login), 'login');  
        } else {
            $title = sprintf( __('[%s] Password Reset'), $blogname );
            $message = __('Someone requested that the password be reset for the following account:') . '<br/>';
            $message .= network_home_url( '/' ) . '<br/>';
            $message .= sprintf(__('Username: %s'), $user_login) . '<br/>';
            $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . '<br/>';
            $message .= __('To reset your password, visit the following address:') . '<br/>';
            $message .= network_site_url('wp-login.php?action=rp&key='.$key.'&login=' . rawurlencode($user_login), 'login');                                            
        }        

        // Final fillter
        $title = apply_filters('retrieve_password_title', $title);
        $message = apply_filters('retrieve_password_message', $message, $key);
        
        /**
         * Send email with queue
         * - Send directly
         * - Send via queue 
         */
        if(Setting_Model::queue_notification()) {
            // Notification will be sent via queue 
            $notification_id = Notification_Model::insert([
                'user_id' => $user_data->ID,
                'title' => $title,
                'content' => $message,
                'email_use' => '1',
                'email_content_type' => 'Content-type: text/html; charset=UTF-8',
                'email_sent' => '0',
                'email_to' => $email,
                'message' => '',
                'mobile_use' => '0',
                'mobile_sent' => '0',
                'created_on' => gmdate('Y-m-d H:i:s'),
            ]);

            if(!$notification_id) {
                return rest_ensure_response([
                    'code' => 'auth_reset_password',
                    'message' => __('Reset password is error. System can not make notification for send to the user.'),
                    'data' => [
                        'status' => 403
                    ]
                ]);
            }
        } else {
            // Send directly via email
            if ( $message && !wp_mail($user_email, $title, $message, $headers)) {
                return rest_ensure_response([
                    'code' => 'auth_reset_password',
                    'message' => __('The e-mail could not be sent.') . __('Possible reason: your host may have disabled the mail() function...'),
                    'data' => [
                        'status' => 403
                    ]
                ]);
            }
        }

        return rest_ensure_response([
            'success' => TRUE,
            'msg' => __('Check your email for the confirmation link.', 'listar')
        ]);
    }

    /**
     * Allow user register account
     * - Default role is 'subscriber'
     *
     * @param WP_REST_Request $request User's request information
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function register($request) {
        $response   = [];
        $username   = sanitize_user($request['username'], true);
        $email      = sanitize_email($request['email']);
        $password   = sanitize_text_field($request['password']);

        try {
            if (empty($username)) {
                throw  new Exception(__('Username field is required. (only has alphanumeric characters (letters and numbers)', 'listar'));
            }
            if (empty($email)) {
                throw new Exception(__('Email field is required.', 'listar'));
            }
            if (empty($password)) {
                throw new Exception('Password field is required.', 'listar');
            }

            // Check exist account
            $user_id = username_exists($username);

            if (!$user_id && email_exists($email) === FALSE) {
                $user_id = wp_create_user($username, $password, $email);
                if (!is_wp_error($user_id)) {
                    $user = get_user_by('id', $user_id);
                    // Set default role > author (If set as subscriber then user can't submit or upload image
                    $user->set_role('author');
                    // WooCommerce > set default role > customer
                    if (class_exists('WooCommerce')) {
                        $user->set_role('customer');
                    }
                    
                    //=== OTP
                    if(Setting_Model::otp_use()) {
                        $otp = new Otp;
                        list($code, $expire_time) = $otp->generate($email);

                        /**
                         * - Created user
                         * - Sending otp code
                         * - Move to next step login & verify otp code
                         */
                        return new WP_Error(
                            'auth_otp_require',
                            __( 'Registration new account was successfully.' ),
                            [
                                'email' => $email,
                                'status' => 200
                            ]
                        );
                    } else {
                        $response['code'] = 200;
                        $response['message'] = __('Registration new account was successfully', 'listar');
                    }
                } else {
                    throw new Exception($user_id->get_error_message());
                }
            } else {
                throw new Exception(__("Email already exists, please try 'Reset Password'", 'wp-rest-user'));
            }

            return new WP_REST_Response($response, 200);
        } catch (Exception $e) {
            return new WP_Error( 'auth_register_error', $e->getMessage(), ['status' => 400 ] );
        }
    }

     /**
     * Return current user's profile
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function user($request) {
        $this->user = wp_get_current_user();

        // Check authorized
        if(!$this->user->ID) {
            return new WP_Error( 'rest_permission', __( 'Permission denied', 'listar' ), ['status' => 200] );
        }

        return rest_ensure_response([
            'success' => true,
            'data' => User_Model::refactor_user_data($this->user->data)
        ]);
    }

    /**
     * Deactivate account
     * - Set role as Non
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function deactivate(WP_REST_Request $request)
    {
        try {
            $this->user = wp_get_current_user();

            if(empty($this->user)) {
                throw new Exception('Invalid account');
            } else {
                if(Setting_Model::get_option('deactivate_account')) {
                    User_Model::delete_account($this->user->ID);
                } else {
                    User_Model::block_account($this->user->ID);
                }
            }

            return rest_ensure_response([
                'success' => TRUE,
                'msg' => __('The account is deactivated successfully and the account can not be used for login the app.')
            ]);
        } catch (Exception $e) {
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        } catch(Throwable $e) {
            return rest_ensure_response([
                'success' => FALSE,
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * OTP request
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.36
     */
    public function otp(WP_REST_Request $request)
    {
        try {
            $email = sanitize_user($request['email'], true);
            $otp = new Otp();
            list($code, $expire_time) = $otp->generate($email);            

            return rest_ensure_response([
                'success' => TRUE,
                'msg' => __('OTP code is sent to your email.', 'listar'),
                'data' => [
                    'exp_time' => $expire_time
                ]
            ]);
        } catch (Exception $e) {
            return rest_ensure_response([
                'success' => FALSE,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Function for `rest_after_insert_user` action-hook.
     *
     * @param WP_User         $user     Inserted or updated user object.
     * @param WP_REST_Request $request  Request object.
     * @param bool            $creating True when creating a user, false when updating.
     * @since 1.0.19
     * @return void
     */
    public function rest_after_insert_user($user, $request, $creating) {
        // User Photo ID
        if(isset($_POST['listar_user_photo'])) {
            update_user_meta($user->ID, User_Model::$user_photo_key, $request->get_param('listar_user_photo'));
        }
    }

    /**
     * Customize data before token dispatch
     *
     * @param array $data
     * @param WP_User $user
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function auth_token_before_dispatch($data, $user) {
        try {        
            //=== OTP
            if (is_wp_error($error = $this->otp_validation($user->data->user_email))) {
                return $error;
            }

            //=== Payload
            $response = User_Model::refactor_user_data($user);
            $response['token'] = $data['token'];
            $response['exp'] = $this->exp;

            // Prevent login of the account is active or set non role
            if (User_Model::is_blocked($user)) {
                throw new Exception(__('The account has been deactivated'));
            }

            // Check & login user's device token
            $headers = getallheaders();
            if (isset($headers['Device-Id'])) {
                $token_data = [
                    'device_id' => sanitize_text_field($headers['Device-Id']),
                    'device_model' => isset($headers['Device-Model']) ? sanitize_text_field($headers['Device-Model']) : '',
                    'device_version' => isset($headers['Device-Version']) ? sanitize_text_field($headers['Device-Version']) : '',
                    'push_token' => isset($headers['Device-Token']) ? sanitize_text_field($headers['Device-Token']) : '',
                    'type' => isset($headers['Type']) ? sanitize_text_field($headers['Type']) : ''
                ];

                User_Model::set_token($user->ID, $token_data);
            }

            // Generate gps point
            if(defined('WP_LISTAR') && WP_LISTAR) {
                if(isset($headers['latitude']) && isset($headers['longitude'])) {
                    $random_places = Place_Model::get_recent_data([
                        'orderby' => 'rand'
                    ]);
                    foreach($random_places as $row) {
                        $gps_point = listar_gps_random_point([$headers['latitude'], $headers['longitude']], 10);
                        update_post_meta($row->ID, 'latitude', $gps_point[0]);
                        update_post_meta($row->ID, 'longitude', $gps_point[1]);
                    }
                }
            }

            return [
                'success' => TRUE,
                'message' => '',
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => FALSE,
                'message' => $e->getMessage(),
                'data' => []
            ];
        } catch(Throwable $e) {
            return rest_ensure_response([
                'success' => FALSE,
                'data' => [],
                'msg' => $e->getMessage()
            ]);
        }
    }

    /**
     * Customize html code message
     *
     * @since 1.0.20
     * @param $id
     * @param $error
     */
    public function auth_login_failed( $id, $error) {
        $code = $error->get_error_code();
        $error->remove($code);
        $error->add($code, __('Invalid username or incorrect password.'), []);
    }

    /**
     * Extends auth expire days
     * @params int $exp expire time default is 7 days 
     */
    public function auth_expire($exp) {
        $jwt_auth_expire = (int)Setting_Model::get_option('jwt_auth_expire');
        if($jwt_auth_expire > 0) {
            $days = $jwt_auth_expire;
            $exp = time() + (86400 * $days); // 86400 = 24 hours in 	 		
        }
        return $exp;
    }

    /**
     * Check data before encode
     *
     * @param array $token
     * @param WP_User $user
     * @return array
     */
    public function auth_token_before_sign($token, $user) {    
        // Token expired
        $this->exp = $token['exp'];
        return $token;
    }

    /**
     * Undocumented function
     * @param string $email
     * @return WP_Error|boolean
     * @throwss Exception
     */
    protected function otp_validation($email = '') 
    {
        if(Setting_Model::otp_use()) {
            if(!isset($_POST['code']) || $_POST['code'] == '') {
                return new WP_Error(
                    'auth_otp_require',
                    __( 'OTP code is required.' ),
                    [
                        'email' => $email,
                        'status' => 200
                    ]
                );
            } else {
                $code = sanitize_text_field($_POST['code']);
                
                try {
                    $otp = new OTP;
                    if($otp->validate($email, $code)) {
                        $otp->set_expired($email);
                    } else {
                        return new WP_Error(
                            'auth_otp_validate',
                            __('OTP code invalid. Please try again'),
                            [
                                'email' => $email,
                                'status' => 200
                            ]
                        );
                    }
                } catch(Exception $e) {
                    return new WP_Error(
                        'auth_otp_error',
                        $e->getMessage(),
                        [
                            'email' => $email,
                            'status' => 200
                        ]
                    );
                }
                
            }            
        }

        return true;
    }
}
