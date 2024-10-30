<?php
namespace ListarWP\Plugin\Commands;

use ListarWP\Plugin\Libraries\Command_Interface;
use ListarWP\Plugin\Models\Notification_Model;
use ListarWP\Plugin\Models\Setting_Model;
use Exception;

class Notify_Command implements Command_Interface {

    protected $environment;

    /**
     * Wordpress CLI command root 
     *
     * @var string
     */
    protected $wp_cli_bin = '/usr/local/bin/wp';

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
        return 'listar-notify';    
    }

    /**
     * Construct 
     */
    public function __construct( ) {
        $this->environment = wp_get_environment_type();
        if(defined('WP_CLI_BIN')) {
            $this->wp_cli_bin = WP_CLI_BIN;
        }        
    }

     /**
     * Run validate
     */
    public function run($args,  $assoc_args)
    {       
        try {
            $args = wp_parse_args($assoc_args, ['action' => '']);
            $root_path = getcwd();
            
            // Check process is being run
            exec("ps aux | grep 'wp listar-notify email' | grep -v grep", $pids_email);
            exec("ps aux | grep 'wp listar-notify mobile' | grep -v grep", $pids_mobile);
            
            $total_process = count($pids_email) + count($pids_mobile);
            if ($total_process > 0) {
                echo "Processing is running ({$total_process})".PHP_EOL;
                exit();
            } else {
                echo "Trigger sending message ...".PHP_EOL;
                $rows = Notification_Model::get_list_for_send('id, email_use, email_sent, mobile_use, mobile_sent');

                if(!empty($rows)) {
                    foreach($rows as $notification) {
                        if($notification->email_use == '1' && $notification->email_sent == '0') {
                            exec('cd '.$root_path.'; '.$this->wp_cli_bin.' listar-notify email --id='.$notification->id.' > /dev/null &');
                        }

                        if($notification->mobile_use == '1' && $notification->mobile_sent == '0') {
                            exec('cd '.$root_path.'; '.$this->wp_cli_bin.' listar-notify mobile --id='.$notification->id.' > /dev/null &');
                        }
                    }
                }
            }
        } catch(Exception $e) {
            error_log('listar.notify_command.run: '.$e->getMessage());
        }
    }

    /**
     * Send notification
     *
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public function email($args,  $assoc_args) 
    {
        $args = wp_parse_args($assoc_args, ['id' => 0]);
        $error = [];

        try {
            $id = (int) $args['id'];
            if(!$id) {
                throw new Exception('Undefine notification ID for send.');
            }

            // Get data by ID
            $notification = Notification_Model::get_notification_for_send($id, 
                "title, content, email_to, email_cc, email_content_type",
                "1"
            );            
            if(!$notification) {
                throw new Exception('Undefine notification data for send.');           
            }

            // Header
            $headers = [];
            if($notification->email_content_type ) {
                $headers[] = $notification->email_content_type;  
            } else {
                $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            }

            // CC
            $cc = json_decode($notification->email_cc);
            if(is_array($cc) && !empty($cc)) {
                foreach($cc as $email_cc) {
                    $headers[] = 'Cc: '.$email_cc;   
                }
            }

            // Send
            if(!wp_mail($notification->email_to, $notification->title, $notification->content, $headers)) {
                $error = 'wp_mail() function is not working';
                error_log('listar.notify_command.user.email: '.$error.PHP_EOL);
            }            
        } catch (Exception $e) {
            // Console log
            $error = $e->getMessage();
            error_log('listar.notify_command.user: '.$error);
        } finally {
            // Update send status
            Notification_Model::update_status($id, [
                'email_sent' => '1',
                'email_sent_on' => date('Y-m-d H:i:s'),
                'email_error' => $error,
            ]);
        }
    }

    /**
     * Send firebase notification
     *
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    public function mobile($args,  $assoc_args) 
    {
        $args = wp_parse_args($assoc_args, ['id' => 0]);
        $error = '';
        
        try {
            $id = (int) $args['id'];
            if(!$id) {
                throw new Exception('Undefine notification ID for send.');
            }

            // Get data by ID
            $notification = Notification_Model::get_notification_for_send($id, 
                "title, message, mobile_token, meta_data", 
                "1"
            );            

            if(!$notification) {
                throw new Exception('Undefine notification data for send.');           
            }

            // Firebase: send        
            $subject = $notification->title;
            $message = $notification->message;
            $tokens = json_decode($notification->mobile_token);
            $data = json_decode($notification->meta_data);

            // Firebase: token
            if(Setting_Model::get_option('mobile_push_debug_use')) {
                $debug_token = Setting_Model::get_option('mobile_push_debug_token');
                $tokens = explode(',', $debug_token);
            }

            if(is_array($tokens) && !empty($tokens)) {
                foreach($tokens as $token) {
                    try {
                        $push = listar_mobile_push_message($token, $subject, $message, $data);   
                        if(isset($push['error'])) {
                            throw new Exception($push['error']['message']. ' (Code='.$push  ['error']['code'].') & Token='.$token);
                        }
                    } catch (Exception $e) {
                        $error = $e->getMessage()."(ID:{$id})";
                        error_log('listar.notify_command.mobile: '.$error);
                    }
                }
            } else {
                $error = "Token data was not found (ID:{$id})";
                error_log('listar.notify_command.mobile: '.$error);
            }                          
        } catch (Exception $e) {
            // Console log
            $error = $e->getMessage();
            error_log('listar.notify_command.mobile: '.$error);
        } finally {
            // Update db status
            Notification_Model::update_status($id, [
                'mobile_sent' => '1',
                'mobile_sent_on' => date('Y-m-d H:i:s'),
                'mobile_error' => $error
            ]);
        }
    }
}