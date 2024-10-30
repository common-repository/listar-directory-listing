<?php
namespace ListarWP\Plugin\Models;
use Exception;

class Otp_Model {

    /**
     * table booking item
     * @var string
     */
    public static $table_suffix = 'listar_otp';

    /**
     * Get table name
     * @return string
     */
    public static function get_table()
    {
        global $wpdb;
        return $wpdb->prefix.self::$table_suffix;
    }

    /**
     * Insert otp code
     * - Set expire all and insert again
     * 
     * @param int $user_id User Id
     * @param string $email User Email
     * @param string $code OTP Code
     * @return array [int, int] 
     * @throws Exception
     */
    public static function insert($user_id = 0, $email = '', $code = '')
    {
        // Set expire before insert
        self::set_expired($email);

        // Insert new 
        global $wpdb;
        $expire_time = (int)Setting_Model::get_option('otp_expire_time');
        if(!$expire_time) {
            $expire_time = 60;
        }
        
        $wpdb->insert(self::get_table(), [
            'user_email' => $email,
            'user_id' => $user_id,
            'code' => $code,
            'expired' => '0', // not use 
            'created_on' => date('Y-m-d H:i:s'),
            'expired_on' => date('Y-m-d H:i:s', strtotime("+{$expire_time} sec")),
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);

        if(!$wpdb->insert_id) {
            throw new Exception(__('Create OTP code error.'));
        }
        
        return [$wpdb->insert_id, $expire_time];
    }

    /**
     * Validate code OTP
     *
     * @param string $email
     * @param string $code
     * @return boolean|stdClass
     * @throws Exception
     */
    public static function valid($email = '', $code = '')
    {
        global $wpdb;
        $sql = "SELECT * FROM ".self::get_table()." 
            WHERE user_email = '{$email}'
            AND code = '{$code}'
            AND expired = '0'
            ORDER BY id 
            LIMIT 1
        ";
        return $wpdb->get_row($sql);
    }

    /**
     * Set expired 
     *
     * @param string $email
     * @param string $expired
     * @return int|false
     */
    public static function set_expired($email = '', $expired = '1') 
    {
        global $wpdb;
        return $wpdb->update( 
            self::get_table(), 
            ['expired' => $expired], 
            ['user_email' => $email]
        );
    }
}