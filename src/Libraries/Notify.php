<?php
namespace ListarWP\Plugin\Libraries;

use ListarWP\Plugin\Models\Setting_Model;
use ListarWP\Plugin\Models\Notification_Model;
use ListarWP\Plugin\Models\User_Model;
use Exception;

class Notify {
    /**
     * Setting key by email prefix
     * @var string
     */
    public static $email_setting_key = '';

    /**
     * recipient
     * @var string|array
     */
    public static $recipient = [];

    /**
     * CC
     * @var string|array
     */
    public static $cc = [];

    /**
     * Mobile push token
     *
     * @var array
     * @since 1.0.21
     */
    public static $mobile_push_token = [];

    /**
     * Map data with string pattern
     *
     * @param array $array
     * @param string $string
     * @return string
     * @since 1.0.12
     */
    static function pattern_replace($array, $string = '') {
        return listar_pattern_replace($array, $string);
    }

    /**
     * Log notify on db
     *
     * @param array $log_index user index id
     * @param array $data
     * @return boolean
     */
    public static function notify_log(array $log_index = [],  array $data = []) 
    {
        $notification = [];
        $notify_email_use = Setting_Model::get_option('email_use'); // common setting
        $email_use = Setting_Model::get_option('email_'.self::$email_setting_key.'_use'); // check base on case

        $mobile_push_use = Setting_Model::get_option('mobile_push_use'); // common setting
        $push_use = Setting_Model::get_option('push_'.self::$email_setting_key.'_use'); // check base on case

        // Meta data include
        $data['site_title'] = get_bloginfo();
        $data['site_address'] = get_site_url();

        // Disable all option & return
        if(!$notify_email_use && !$mobile_push_use) {
            return;
        }

        // Subject
        $subject = Setting_Model::get_option('email_'.self::$email_setting_key.'_subject');
        $subject = self::pattern_replace($data, $subject);
        $content = '';
        $content_type = '';
        
        //== Email
        if($notify_email_use && ((int) $email_use === 1 || (string) $email_use === 'true')) {
            // Content
            $content = Setting_Model::get_option('email_'.self::$email_setting_key.'_content');
            $content = self::pattern_replace($data, $content);

            // Email type
            $content_type = Setting_Model::get_option('email_'.self::$email_setting_key.'_type') == 'plain' ?
                'Content-Type: text/plain; charset=UTF-8' : 'Content-Type: text/html; charset=UTF-8';

        }

        // === Firebase
        $message = '';
        if($mobile_push_use && ((int) $push_use === 1 || (string) $push_use === 'true')) {
            // Message
            $message = Setting_Model::get_option('push_'.self::$email_setting_key.'_content');
            $message = self::pattern_replace($data, $content);
        }   

        if(!empty($log_index)) {
            foreach($log_index as $user_id) {
                $email_to = isset(self::$recipient[$user_id]) ? self::$recipient[$user_id] : NULL;
                $tokens = isset(self::$mobile_push_token[$user_id]) ? self::$mobile_push_token[$user_id] : NULL;
                
                $notification = [
                    'user_id' => $user_id,
                    'title' => $subject,
                    'content' => $content,
                    'email_use' => $notify_email_use && $email_use ? '1' : '0',
                    'email_content_type' => $content_type,
                    'email_sent' => '0',
                    'email_to' => $email_to,
                    'email_cc' => !empty(self::$cc) ? json_encode(self::$cc) : '',
                    'email_error' => '',
                    'message' => $message,
                    'mobile_use' => $mobile_push_use && $push_use ? '1' : '0',
                    'mobile_sent' => '0',
                    'mobile_token' => is_array($tokens) && !empty($tokens) ? json_encode($tokens) : '',
                    'mobile_error' => '',
                    'created_on' => date('Y-m-d H:i:s'),
                ];
                
                Notification_Model::insert($notification);                
            }
        } else {
            error_log('notify.log: Undefined log index.'.PHP_EOL);
        }    
    }

    /**
     * Send notification directly
     * - Mail
     * - Firebase push
     * 
     * @param array $data
     * @param array $data
     * @since 1.0.12
     */
    public static function notify(int $user_id, array $data = [])
    {
        $headers = [];        
        $notify_email_use = Setting_Model::get_option('email_use'); // common setting
        $email_use = Setting_Model::get_option('email_'.self::$email_setting_key.'_use'); // check base on case

        $mobile_push_use = Setting_Model::get_option('mobile_push_use'); // common setting
        $push_use = Setting_Model::get_option('push_'.self::$email_setting_key.'_use'); // check base on case

        // log: data sent
        if(WP_DEBUG === TRUE) {
            error_log('notify.email_use: ' . $notify_email_use . PHP_EOL);
            error_log('notify.mobile_push_use: ' . $mobile_push_use . PHP_EOL);
            error_log('notify.data: ' . json_encode($data) . PHP_EOL);
        }

        // function: disable & return
        if(!$notify_email_use && !$mobile_push_use) {
            return;
        }

        // metadata
        $data['site_title'] = get_bloginfo();
        $data['site_address'] = get_site_url();

        // data: subject
        $subject = Setting_Model::get_option('email_'.self::$email_setting_key.'_subject');
        $subject = self::pattern_replace($data, $subject);

        // email: sent status
        $email_trigger = $notify_email_use && ((int) $email_use === 1 || (string) $email_use === 'true');

        // email: content
        $content_template = Setting_Model::get_option('email_'.self::$email_setting_key.'_content');
        $content = self::pattern_replace($data, $content_template);

        // email: content type
        $content_type = Setting_Model::get_option('email_'.self::$email_setting_key.'_type');

        // email: header
        if($content_type == 'plain') {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }

        // email: recipient
        if(!self::$recipient || !is_string(self::$recipient)) {
            throw new Exception('Email recipient is invalid data (string required).');
        }
        // email: cc
        if(is_array(self::$cc) && !empty(self::$cc)) {
            foreach(self::$cc as $cc) {
                $headers[] = 'Cc: '.$cc;   
            }
        }

        // email: log
        if(Setting_Model::get_option('email_debug_use')) {    
            error_log('email.headers: ' . json_encode($headers) . PHP_EOL);                    
            error_log('email.recipient: ' . self::$recipient . PHP_EOL);
            error_log('email.subject: ' . $subject . PHP_EOL);
            error_log('email.content: ' . $content . PHP_EOL);            
        }

        // firebase: send status
        $mobile_trigger = $mobile_push_use && ((int) $push_use === 1 || (string) $push_use === 'true');

        // firebase: content
        $content_template = Setting_Model::get_option('push_'.self::$email_setting_key.'_content');
        $body = self::pattern_replace($data, $content_template);

        // firebase: token
        if(Setting_Model::get_option('mobile_push_debug_use')) {
            $debug_token = Setting_Model::get_option('mobile_push_debug_token');
            self::$mobile_push_token = explode(',', $debug_token);
        }

        // firebase: log
        if(Setting_Model::get_option('mobile_push_debug_use')) {
            error_log('firebase.title: ' . $subject . PHP_EOL);
            error_log('firebase.body: ' . $body . PHP_EOL);
            error_log('firebase.token: ' . json_encode(self::$mobile_push_token) . PHP_EOL);
        }

        // notification: log
        $notification = [
            'user_id' => $user_id,
            'title' => $subject,
            'content' => $content,
            'meta_data' => json_encode($data),
            'email_use' => $email_trigger ? '1' : '0',
            'email_content_type' => $content_type,
            'email_sent' => '0',
            'email_to' => self::$recipient,
            'email_cc' => !empty(self::$cc) ? json_encode(self::$cc) : '',
            'message' => $body,
            'mobile_use' => $mobile_trigger ? '1' : '0',
            'mobile_sent' => '0',
            'mobile_token' => is_array(self::$mobile_push_token) && !empty(self::$mobile_push_token) ? json_encode(self::$mobile_push_token) : '',
            'created_on' => gmdate('Y-m-d H:i:s'),
        ];
        $notification_id = Notification_Model::insert($notification);
        
        // notification: run as job queue or send directly
        if(Setting_Model::queue_notification()) {
            return;
        } else {
            // email: 
            if($email_trigger) {
                try {
                    // email: validation
                    if(!self::$recipient) {
                        throw new Exception('Missing recipient for sent');
                    }

                    if(!$subject) {
                        throw new Exception('Missing subject for sent');
                    }

                    if(!$content) {
                        throw new Exception('Missing content for sent');
                    }

                    // email: recipient duplicate remove  
                    if(is_array(self::$recipient) && !empty(self::$recipient)) {
                        self::$recipient = array_unique(self::$recipient);
                    }

                    // email: send
                    if(!wp_mail(self::$recipient, $subject, $content, $headers)) {
                        error_log('wp_mail.error: wp_mail() function is not working'.PHP_EOL);
                    }
                } catch (Exception $e) {
                    error_log('wp_mail.error: '.$e->getMessage());
                } finally {
                    Notification_Model::update_status($notification_id, [
                        'email_sent' => '1',
                    ]);
                }
            }

            // firebase: 
            if($mobile_trigger) {
                try {
                    // firease: validation
                    if(empty(self::$mobile_push_token)) {
                        throw new Exception(__('Firebase token is empty.'));
                    } 

                    if(empty($subject) || empty($body)) {
                        throw new Exception('Firease subject or body invalid data.');
                    }

                    // firebase: send
                    foreach(self::$mobile_push_token as $token) {
                        $result = [];
                        try {
                            $result = listar_mobile_push_message($token, $subject, $body, $data);
                        } catch(Exception $e) {
                            // firebase: exception
                            if(is_array($result) && isset($result['error'])) {
                                error_log('firebase.push_error: '.$result['error']['message']. ' (Code='.$result  ['error']['code'].')');
                            }
                        } finally {
                            // firebase: log
                            if(Setting_Model::get_option('mobile_push_debug_use')) {
                                error_log('firebase.result: ' . json_encode($result) . PHP_EOL);
                            }
                        }
                    }           
                } catch (Exception $e) {
                    error_log('firebase.error:'. $e->getMessage().PHP_EOL);
                } finally {
                    Notification_Model::update_status($notification_id, [
                        'mobile_sent' => '1',
                    ]);
                }
            }    
        }    
    }

    /**
     * Send notification when the booking is created
     * @param array $booking
     * @since 1.0.12
     */
    public static function notify_create_booking(array $booking = [])
    {   
        // email: setting key
        self::$email_setting_key = 'order';

        // email: to (owner)
        if(isset($booking['author_email']) && !empty($booking['author_email'])) {
            self::$recipient = $booking['author_email']; 
        }                

        // email: cc setting
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient'); // General Setting
        }
        if(!empty($cc)) {
            self::$cc[] = $cc;   
        }

        // email: cc billing
        if(Setting_Model::get_option('email_order_user_use')) {
            self::$cc[] = $booking['billing_email'];
        }

        // metadata: booking
        $booking['type'] = 'booking';
        $booking['action'] = self::$email_setting_key;

        // firebase: token
        if(isset($booking['author'])) {
            $user_tokens = User_Model::get_all_device_token(['include' => $booking['author']]);
            self::$mobile_push_token = isset($user_tokens[$booking['author']]) ? $user_tokens[$booking['author']] : [];
        }

        // notify: send 
        self::notify((int) $booking['author'], $booking);
    }

    /**
     * Send notification when the booking is canceled
     * @param array $booking
     * @since 1.0.12
     */
    public static function notify_cancel_booking(array $booking = [])
    {
        self::$email_setting_key = 'cancel';

        // medata
        $booking['type'] = 'booking';
        $booking['action'] = self::$email_setting_key;        

        // firebase: tokens
        $user_tokens = [];
        if(isset($booking['author']) && isset($booking['customer_user'])) {
            $user_tokens = User_Model::get_all_device_token(['include' => [
                $booking['author'],
                $booking['customer_user']
            ]]);
        }

        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient'); // module setting
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient'); // commmon setting
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }
        
        // ==== Owner
        // email: to owner
        if(isset($booking['author_email']) && !empty($booking['author_email'])) {
            self::$recipient = $booking['author_email'];
        }        

        // notify: owner
        self::$mobile_push_token = isset($user_tokens[$booking['author']]) ? $user_tokens[$booking['author']] : [];
        self::notify((int) $booking['author'], $booking);

        // ==== User
        // email: to user
        if(isset($booking['billing_email']) && !empty($booking['billing_email'])) {
            self::$recipient = $booking['billing_email'];
        } 

        // notify: user
        self::$mobile_push_token = isset($user_tokens[$booking['customer_user']]) ? $user_tokens[$booking['customer_user']] : [];
        self::notify((int) $booking['customer_user'], $booking);
    }

    /**
     * Send notification when fail booking with some reason
     * @param array $booking
     * @since 1.0.12
     */
    public static function notify_fail_booking(array $booking = [])
    {
        self::$email_setting_key = 'fail';

        // metadata
        $booking['type'] = 'booking';
        $booking['action'] = self::$email_setting_key;

        // firebase: tokens
        $tokens = [];
        if(isset($booking['author']) && isset($booking['customer_user'])) {
            $tokens = User_Model::get_all_device_token(['include' => [
                $booking['author'],
                $booking['customer_user']
            ]]);
        }
        
        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient');
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }

        // notify: owner
        if(isset($booking['author_email']) && !empty($booking['author_email'])) {
            self::$recipient = $booking['author_email'];
        } 
        self::$mobile_push_token = isset($tokens[$booking['author']]) ? $tokens[$booking['author']] : [];
        self::notify((int) $booking['author'], $booking);

        // notify: user
        if(isset($booking['billing_email']) && !empty($booking['billing_email'])) {
            self::$recipient = $booking['billing_email'];
        } 
        self::$mobile_push_token = isset($tokens[$booking['customer_user']]) ? $tokens[$booking['customer_user']] : [];
        self::notify((int) $booking['customer_user'], $booking);
    }

    /**
     * Send notification when the booking is completed
     * @param string $billing_email email of customer
     * @param array $booking
     * @since 1.0.12
     */
    public static function notify_complete_booking(string $billing_email = '', array $booking = [])
    {
        // notify: setting key
        self::$email_setting_key = 'complete';

        // notify: metadata
        $booking['type'] = 'booking';
        $booking['action'] = self::$email_setting_key;

        // firebase: tokens
        $tokens = [];
        if(isset($booking['author']) && isset($booking['customer_user'])) {
            $tokens = User_Model::get_all_device_token(['include' => [
                $booking['author'],
                $booking['customer_user']
            ]]);
        }

        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient');
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }

        // notify: owner
        if(isset($booking['author_email']) && !empty($booking['author_email'])) {
            self::$recipient = $booking['author_email'];
        }
        self::$mobile_push_token = isset($tokens[$booking['author']]) ? $tokens[$booking['author']] : [];
        self::notify((int) $booking['author'], $booking);

        // notify: user
        self::$mobile_push_token = isset($tokens[$booking['customer_user']]) ? $tokens[$booking['customer_user']] : [];
        self::$recipient = $billing_email;
        self::notify((int) $booking['customer_user'], $booking);
    }

    /**
     * Send notification when admin change booking status
     * @param array $booking
     * @param string $email_recipient
     * @since 1.0.21
     */
    public static function notify_status_booking(array $booking = [], $email_recipient = '')
    {
        // notify: setting key
        self::$email_setting_key = 'status';

        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient');
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }

        // email: to user
        if(!empty($email_recipient)) {
            self::$recipient = $email_recipient;
        }

        // firebase: token
        $tokens = [];
        if(isset($booking['customer_user'])) {
            $tokens = User_Model::get_all_device_token(['include' => $booking['customer_user']]);
        }
        self::$mobile_push_token = isset($tokens[$booking['customer_user']]) ? $booking['customer_user'] : [];
        self::notify((int) $booking['customer_user'], $booking);
    }

    /**
     * Send notification when user submit new listing
     * - Add new via mobile 
     * - Add new via website 
     * 
     * @param array $post
     * @since 1.0.13
     */
    public static function notify_claim_submit(array $post)
    {
        // notify: key setting
        self::$email_setting_key = 'claim_submit';

        // notify: metadata
        $post['type'] = 'claim';
        $post['action'] = self::$email_setting_key;
        
        // email: cc
        self::$cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!self::$cc) {
            self::$cc = Setting_Model::get_option('email_recipient');
        }

        // firebase: tokens
        $tokens = User_Model::get_all_device_token([ 'role__in' => ['administrator']]);
        
        // notify: to administrators
        $administrators = get_users([ 'role__in' => ['administrator']]);
        if(!empty($administrators)) {
            foreach($administrators as $user) {
                self::$mobile_push_token = isset($tokens[$user->ID]) ? $tokens[$user->ID] : [];
                self::$recipient = $user->data->user_email;
                self::notify((int) $user->ID, $post);
            }
        }            
    }

    /**
     * Claim to request listing
     * @param array $data
     * @since 1.0.31
     */
    public static function notify_claim_request(array $data = [])
    {   
        // notify: key setting
        self::$email_setting_key = 'claim_request';

        // notify: metadata
        $data['type'] = 'claim';
        $data['action'] = self::$email_setting_key;

        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient');
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }

        // email: cc to user as optional
        if(Setting_Model::get_option('email_'.self::$email_setting_key.'_user_use')) {
            self::$cc[] = $data['billing_email'];
        }

        // notify: author 
        if(isset($data['author'])) {
            // firebase: token
            $tokens = [];
            $tokens = User_Model::get_all_device_token(['include' => $data['author']]);
            self::$mobile_push_token = isset($tokens[$data['author']]) ? $tokens[$data['author']] : [];
            
            // email: owner
            if(isset($data['author_email']) && !empty($data['author_email'])) {
                self::$recipient = $data['author_email'];
            }  
            // notify: owner
            self::notify((int) $data['author'], $data);
        }
    }

    /**
     * Request to claim is approved
     * 
     * @param array $post
     * @since 1.0.31
     */
    public static function notify_claim_approve(array $post = [])
    {   
        $user_id = $post['post_author'];

        // notify: setting key
        self::$email_setting_key = 'claim_approve';

        // notify: metadata
        $post['type'] = 'claim';
        $post['action'] = self::$email_setting_key;

        // email: author
        $author = get_userdata($user_id);
        self::$recipient = $author->data->user_email;

        // firebase: token
        $tokens = User_Model::get_all_device_token(['include' => $user_id]);
        self::$mobile_push_token = isset($tokens[$user_id]) ? $tokens[$user_id] : [];

        // notify: send
        self::notify((int) $user_id, (array) $post);
    }

    /**
     * Claim listing is completed : change status or payment
     * @param array $data
     * @since 1.0.31
     */
    public static function notify_claim_complete(array $data = [])
    {   
        // notify: setting key
        self::$email_setting_key = 'claim_complete';

        // notify: metadata
        $data['type'] = 'claim';
        $data['action'] = self::$email_setting_key;

        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient');
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }

        // firebase: tokens
        $tokens = [];
        if(isset($data['author']) && isset($data['customer_user'])) {
            $tokens = User_Model::get_all_device_token(['include' => [
                $data['author'],
                $data['customer_user']
            ]]);
        }

        // email: owner
        self::$mobile_push_token = isset($tokens[$data['author']]) ? $tokens[$data['author']] : [];
        if(isset($data['author_email']) && !empty($data['author_email'])) {
            self::$recipient = $data['author_email'];
        }
        self::notify((int) $data['author'], $data);

        // email: user
        self::$mobile_push_token = isset($tokens[$data['customer_user']]) ? $tokens[$data['customer_user']] : [];
        if(isset($data['billing_email']) && !empty($data['billing_email'])) {
            self::$recipient = $data['billing_email'];
        }
        self::notify((int) $data['customer_user'], $data);
    }

    /**
     * Claim is cancelled when processing the payment
     * @param array $booking
     * @since 1.0.31
     */
    public static function notify_claim_cancel(array $data = [])
    {
        // notify: setting key
        self::$email_setting_key = 'claim_cancel';

        // notify: metadata
        $data['type'] = 'claim';
        $data['action'] = self::$email_setting_key;

        // email: cc
        $cc = Setting_Model::get_option('email_'.self::$email_setting_key.'_recipient');
        if(!$cc) {
            $cc = Setting_Model::get_option('email_recipient');
        }

        if(!empty($cc)) {
            self::$cc[] = $cc;
        }
        
        // firebase: tokens
        $tokens = [];
        if(isset($data['author']) && isset($data['customer_user'])) {
            self::$mobile_push_token = User_Model::get_all_device_token(['include' => [
                $data['author'],
                $data['customer_user']
            ]]);
        }

        // notify: owner
        if(isset($data['author_email']) && !empty($data['author_email'])) {
            self::$recipient = $data['author_email'];
        }
        self::$mobile_push_token = isset($tokens[$data['author']]) ? $tokens[$data['author']] : [];
        self::notify((int) $data['author'], $data);

        // notify: user 
        if(isset($data['billing_email']) && !empty($data['billing_email'])) {
            self::$recipient = $data['billing_email'];
        } 
        self::$mobile_push_token = isset($tokens[$data['customer_user']]) ? $tokens[$data['customer_user']] : [];
        self::notify((int)$data['customer_user'], $data);
    }
}
