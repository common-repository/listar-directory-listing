<?php
namespace ListarWP\Plugin\Models;

class Wishlist_Model {

    /**
     * Define variables for metadata key
     * Information will be store in table $prefix_usermeta
     * This meta is private is add underscore '_' with meta keys
     * @var string
     */
    public static $meta_key = '_like_post';

    /**
     * Save data
     *
     * @param integer $user_id
     * @param integer $post_id
     * @return boolean
     * @author Paul <paul.passionui@gmail.com>
     */
    static function save($user_id, $post_id) {
        if(self::check_exist($user_id, $post_id)) {
            // Do nothing
        } else {
            return add_user_meta($user_id, self::$meta_key, $post_id);
        }
        return TRUE;
    }

    /**
     * Remove
     *
     * @param integer $user_id
     * @param integer $post_id
     * @return boolean
     * @author Paul <paul.passionui@gmail.com>
     */
    static function remove($user_id, $post_id) {
        return delete_user_meta($user_id, self::$meta_key, $post_id);
    }

    /**
     * Reset
     *
     * @param integer $user_id
     * @return boolean
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function reset($user_id) {
        return delete_user_meta($user_id, self::$meta_key);
    }

    /**
     * Check exist
     *
     * @param integer $user_id
     * @param integer $post_id
     * @return boolean
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function check_exist($user_id, $post_id) {
        global $wpdb;
        $meta_data = $wpdb->get_row("SELECT * 
            FROM {$wpdb->prefix}usermeta 
            WHERE user_id = {$user_id}
            AND meta_key = '".self::$meta_key."'    
            AND meta_value = {$post_id}
            LIMIT 1
        ", OBJECT );
        return isset($meta_data->umeta_id) ? TRUE : FALSE;
    }
}
