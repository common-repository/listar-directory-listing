<?php
namespace ListarWP\Plugin\Commands;

use ListarWP\Plugin\Libraries\Command_Interface;
use Exception;
use ListarWP\Plugin\Models\Setting_Model;
use WP_Query;
use \WP_CLI;

class Validate_Command implements Command_Interface {

    protected $environment;

    /**
     * Errors
     */
    static $errors = [];

    /**
     * Warnings
     */
    static $warning = [];

    /**
     * Command name
     * @return string
     */
    public static function command_name()
    {
        return 'listar-validate';    
    }

    public function __construct( ) {
        $this->environment = wp_get_environment_type();
    }

     /**
     * Run validate
     */
    public function run($args,  $assoc_args)
    {       
        try {
            $args = wp_parse_args(
                $assoc_args,
                array(
                    'action' => '',
                    'token'  => '',
                )
            );
            
            $site_url = get_site_url();
            $email = $args['email'];
            $token = $args['token'];

            // Plugins
            $active_plugins = array_map('dirname', get_option('active_plugins'));
            $listar_plugin = 'listar-directory-listing'; 

            if ( ! in_array( $listar_plugin , $active_plugins ) ) {
                throw new Exception('Please install plugin Listar Directory Listing https://wordpress.org/plugins/listar-directory-listing/');
            }                                

            // JWT
            if(!in_array('jwt-authentication-for-wp-rest-api', $active_plugins)) {
                self::$warning[] = 'Missing install plugin JWT Authentication for WP REST API (https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)';
            }

            if(!defined('JWT_AUTH_SECRET_KEY')) {
                self::$warning[] = 'Please define JWT_AUTH_SECRET_KEY on wp-config.php for make REST API working. https://listarapp.com/docs/listar-fluxpro/backend-installation/wordpress-plugin/';
            }

            // Firebase 
            if(!defined('LISTAR_FIREBASE_HTTP_V1_KEY')) {
                self::$warning[] = 'Please define LISTAR_FIREBASE_HTTP_V1_KEY on wp-config.php. The Firebase HTTP V1 need define secret key for send push notification.';
            }

            // Setting  
            $setting = new Setting_Model();                        

            // Google map 
            if(empty($setting::get_option('gmap_key'))) {
                self::$warning[] = 'Please define Google map for enable to select gps coordinator.';
            }

            // PayPal 
            $paypal_client_id = $setting::get_option('paypal_client_id');
            $paypal_client_secret = $setting::get_option('paypal_client_secret');
            if(!$paypal_client_id || !$paypal_client_secret) {
                if(!defined('LISTAR_PAYPAL_CLIENT_ID') || !defined('LISTAR_PAYPAL_CLIENT_SECRET')) {
                    self::$warning[] = 'Undefined PayPal client id & client secret. Add wp-config.php LISTAR_PAYPAL_CLIENT_ID and LISTAR_PAYPAL_CLIENT_SECRET or payment settings page. https://listarapp.com/docs/listar-fluxpro/payment/paypal/';                                       
                }
            }

            // Stripe
            $listar_stripe_api_key = $setting::get_option('stripe_api_key');
            if(!$listar_stripe_api_key) {
                if(!defined('LISTAR_STRIPE_API_KEY')) {
                    self::$warning[] = 'Undefined Stripe setting key. Add wp-config.php LISTAR_STRIPE_API_KEY or payment settings page. https://listarapp.com/docs/listar-fluxpro/payment/stripe/';                                       
                }
            }
            
            // Send email 
            if($email) {
                $headers = 'Content-type: text/html; charset=UTF-8' . "\r\n";
                $title = sprintf( __('[%s] WP Email'), $site_url );
                $message = 'Email testing content was sent from '.$site_url;

                if ($message && !wp_mail($email, $title, $message, $headers)) {
                self::$warning[] = 'WP Email is not working. https://listarapp.com/docs/listar-fluxpro/booking/notification-booking/';
                }
            }

            // Firebase
            if(!$token) {
                $token = $setting::get_option('mobile_push_debug_token');
            }    

            if($token) {
                $subject = sprintf( __('[%s] Firebase mobile push testing'), $site_url );
                $content = 'Message testing content was sent from '.$site_url;

                $push = listar_mobile_push_message(
                    $token,
                    $subject,
                    $content,
                    ['foo' => 'bar']
                );   

                if(isset($push['error'])) {
                    self::$warning[] = 'Firebase: '.$push['error']['message']. ' (Code='.$push  ['error']['code'].')';
                }
            }
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        } finally {
            if(!empty(self::$warning)) {
                WP_CLI::warning("==== Please check issues for make system working ===");
                foreach(self::$warning as $index => $msg) {
                    WP_CLI::line(($index+1).'. '.$msg);                    
                }
            } else {
                WP_CLI::success("Validate successfully!");
            }
        }
    }
}