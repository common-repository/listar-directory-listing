<?php
namespace ListarWP\Plugin\Libraries;
use ListarWP\Plugin\Models\Otp_Model;
use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Notification_Model;
use Exception;

class Otp {

    /**
     * User email
     *
     * @var string
     */
    public $email = '';

    /**
     * OTP code
     *
     * @var string
     */
    public $code  = '';

    public function __construct()
    {
        
    }

    /**
     * Generate OTP 
     *
     * @param string $email
     * @param boolean $wp_user_validate check exist WP_User or not
     * @return array [string, int] [code, expire_time]
     * @throws Exception 
     */
    public function generate($email = '', $wp_user_validate = true)
    {
        $user_id = 0;
        // Validate WP_User
        if($wp_user_validate) {
            $user_id = email_exists($email);
            if($user_id === FALSE) {
                error_log('otp.generate.valid_email');
                throw new Exception(__("Email not exists", 'listar'));
            }
        }
        
        $email = sanitize_email($email);
        $code = rand(100000,999999);
        $headers = 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $data = [
            'blogname' => is_multisite() ? $GLOBALS['current_site']->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES),
            'site_title' => get_bloginfo(),
            'site_address' => get_site_url(),
            'user_email' => $email,
            'code' => $code,
            'expire_time' => Setting_Model::get_option('otp_expire_time')
        ];

        $title = listar_pattern_replace($data, Setting_Model::get_option('otp_email_subject'));
        $message = listar_pattern_replace($data, Setting_Model::get_option('otp_email_content'));      

        /**
         * Send email with queue
         * - Send directly
         * - Send via queue 
         */
        if(Setting_Model::queue_notification()) {
            // Notification will be sent via queue 
            $notification_id = Notification_Model::insert([
                'user_id' => $user_id,
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
                throw new Exception(__("[Error#1] OTP sent error.", 'listar'));
            }
        } else {
            // Send directly via email
            if(!wp_mail($email, $title, $message, $headers)) {
                throw new Exception(__("[Error#2] OTP sent error.", 'listar'));
            } 
        }

        return Otp_Model::insert($user_id, $email, $code);
    }       

    /**
     * Validate email + otp code
     *
     * @param string $email
     * @param string $code
     * @throws Exception 
     * @return boolean
     */
    public function validate($email = '', $code = '')
    {
        if(!($otp_code = Otp_Model::valid($email, $code))) {
            throw new Exception(__('OTP code invalid.'));
        }

        if(date('Y-m-d H:i:s') > $otp_code->expired_on) {
            throw new Exception(__('OTP code is expired.'));
        }

        return true;
    }

    /**
     * Set expired 
     *
     * @param string $email
     */
    public function set_expired($email = '')
    {
        if(!Otp_Model::set_expired($email)) {
            throw new Exception(__('OTP code valid is error.'));
        }
    }
}