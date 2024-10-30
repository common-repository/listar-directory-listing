<?php
namespace ListarWP\Plugin\Models;

use Exception;
use ListarWP\Plugin\Listar;
use WP_User;

class User_Model {
    /**
     * meta key for store user token device
     * @var string
     */
    static $token_device_key = 'listar_token_device';

    /**
     * meta key user photo
     * @since 1.0.7
     * @var string
     */
    static $user_photo_key = 'listar_user_photo';

    /**
     * @since 1.0.20
     * Block user account
     * @var string
     */
    static $user_block_account = 'listar_block_account';
    /**
     * Get user's profile
     *
     * @param int $id User ID
     * @return array
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_userdata($id) {
        $user = get_userdata($id);
        return self::refactor_user_data($user->data);
    }

    /**
     * Refactor user's data
     *
     * @param \WP_User $user User's data
     * @return array
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function refactor_user_data($user) {
        $user_meta = get_user_meta( $user->ID, true);
        $result = [
            'id' => (int) $user->ID,
            'user_email' => $user->user_email,
            'user_url' => $user->user_url,
            'user_nicename' => $user->user_nicename,
            'user_level' => (int) get_user_meta($user->ID, 'wp_user_level', TRUE),
            'description' => get_user_meta($user->ID, 'description', TRUE),
            'locale' => get_user_meta($user->ID, 'locale', TRUE),
            'display_name' => $user->display_name,
            'first_name' => get_user_meta($user->ID, 'first_name', true),
            'last_name' => get_user_meta($user->ID, 'last_name', true),
            'user_photo' => self::get_user_photo_url((int) $user->ID, $user->user_email),
            'listar_user_photo' => absint(get_user_meta($user->ID, self::$user_photo_key, TRUE)),
            'guid' => get_author_posts_url($user->ID, $user->user_nicename)
        ];

        return $result;
    }

    /**
     * Get all user push token
     * - Every user may use more than 2 devices
     * @param $query_args array
     * @return array[user.id][token, ...]
     * @version 1.0.3
     */
    static function get_all_device_token($query_args = [])
    {
        $device_token = [];
        if(!empty($query_args)) {
            $all_users = get_users($query_args);
        } else {
            $all_users = get_users();
        }

        if(!empty($all_users)) {
            foreach($all_users as $user) {
                $tokens = get_user_meta($user->ID, User_Model::$token_device_key);
                if(!empty($tokens)) {
                    foreach($tokens as $token) {
                        $token_data = json_decode($token);
                        if($token_data && $token_data->push_token != '') {
                            $device_token[$user->ID][] = $token_data->push_token;
                        }
                    }
                }
            }
        }

        return $device_token;
    }

    /**
     * Get list meta data token as raw
     * @param int $user_id
     * @return array|bool|mixed|object|null
     */
    static function get_raw_token_data($user_id = 0)
    {
        global $wpdb;
        $mid = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s",
            $user_id, self::$token_device_key)
        );
        if( $mid != '' )
            return $mid;

        return false;
    }
    /**
     * @param int $user_id
     * @param array $token_data
     * @version 1.0.3
     */
    static function set_token($user_id = 0, $token_data = [])
    {
        if(Setting_Model::push_single_device()) {
            delete_user_meta($user_id, self::$token_device_key);
            update_user_meta($user_id, self::$token_device_key, json_encode($token_data));
        } else {
            $meta_data = self::get_raw_token_data($user_id);
            $exit = FALSE;
            if(!empty($meta_data)) {
                foreach($meta_data as $row) {
                    $token = json_decode($row->meta_value);
                    if($token_data['device_id'] === $token->device_id) {
                        update_metadata_by_mid('user', $row->umeta_id, json_encode($token_data), self::$token_device_key);
                        $exit = TRUE;
                        break;
                    }
                }
            }

            if(!$exit) {
                add_user_meta($user_id, self::$token_device_key, json_encode($token_data));
            }
        }
    }

    /**
     * Get user photo
     * - Check customize field and return correct photo
     * - User's image > access from gravatar.com
     * - format gravatar.com/avatar/md5({user_email})
     * - Wordpress not support upload user's avata
     * @param int $user_id
     * @param string $user_email
     * @return string
     * @since 1.0.7
     */
    static function get_user_photo_url($user_id = 0, $user_email = '') {
        $image_id = get_user_meta($user_id, self::$user_photo_key, TRUE);
        if( !empty($image_id) ){
            $image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
            if(is_array($image) && !empty($image)) {
                return $image[0];
            }
        } else {
            return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($user_email)));
        }
    }

    /**
     * Check the account is blocked
     * - Check status blocked
     *
     * @since 1.0.19
     * @param WP_User $user
     * @return boolean
     */
    static function is_blocked(WP_User $user) {
        return absint(get_user_meta($user->ID, self::$user_block_account, TRUE)) === 1;
    }

    /**
     * Block account
     * @since 1.0.19
     * @param int $user_id
     */
    static function block_account($user_id) {
        $user = new WP_User($user_id);
        $user->set_role(FALSE);
        update_user_meta($user->ID, User_Model::$user_block_account, 1);
        update_user_meta($user->ID, 'listar_block_time', date('Y-m-d H:i'));
    }

    /**
     * Delete user data
     *
     * @param int $user_id
     */
    static function delete_account($user_id) {
        require_once(ABSPATH.'wp-admin/includes/user.php');

        // Update author 
        $assign_user_id = Setting_Model::get_option('deactivate_account_id');
        $assign_user = get_user_by('login', $assign_user_id);
        if(empty($assign_user)) {
            throw new Exception(__("Undefined the setting assign data other user when deactivate account."));
        }

        $wpdb->query(sprintf("UPDATE %s SET post_author = %s WHERE post_author = %s AND post_type = '%s'", $wpdb->prefix.'posts', 
            $assign_user->ID, $user_id, Listar::$post_type));
        
        // Delete user 
        wp_delete_user($user_id);
    }

    /**
     * Un-block account
     * @since 1.0.19
     * @param int $user_id
     */
    static function unblock_account($user_id) {
        $user = new WP_User($user_id);
        // Close as temp when update profile auto reset the account
        // $user->set_role('author');
        update_user_meta($user->ID, User_Model::$user_block_account, 0);
        update_user_meta($user->ID, 'listar_block_time', '');
    }
}
