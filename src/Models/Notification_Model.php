<?php
namespace ListarWP\Plugin\Models;
use ListarWP\Plugin\Models\Job_Model;
use ListarWP\Plugin\Models\Setting_Model;
use Exception;

class Notification_Model {

    /**
     * Notification table
     * @var string
     */
    public static $table_suffix = 'listar_notifications';

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
     * Insert message
     * 
     * @param array $data
     * @return int insert ID
     * @throws Exception
     */
    public static function insert($data = [])
    {
        // Insert new 
        global $wpdb;
        
        $data['created_on'] = date('Y-m-d H:i:s');
        $data['content'] = str_replace(['<p>', '</p>'], '', $data['content']);
        $data['message'] = str_replace(['<p>', '</p>'], '', $data['message']);
        $wpdb->insert(self::get_table(), $data);

        if(!$wpdb->insert_id) {
            throw new Exception(__('Create notification error.'));
        }

        $insert_id = $wpdb->insert_id;
        
        // job: log for run
        if(Setting_Model::queue_notification()) {
            if($data['email_use'] == '1') {
                Job_Model::insert([
                    'type' => 'cmd',
                    'task' => "listar-notify email --id={$insert_id}",
                ]);
            }

            if($data['mobile_use'] == '1') {
                Job_Model::insert([
                    'type' => 'cmd',
                    'task' => "listar-notify mobile --id={$insert_id}",
                ]);
            }
        }
        return $insert_id;
    }

     /**
     * Get list
     *
     * @param string $select      
     * @param string $where
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function get_list($select = '*', $where = '', $limit = 10)
    {
        global $wpdb;
        $sql = "SELECT {$select} 
            FROM ".self::get_table()." 
            WHERE {$where}
            ORDER BY id ASC
            LIMIT {$limit}
        ";
        
        return $wpdb->get_results($sql);
    }

    /**
     * Get list for send
     * - Use email & hasn't send yet
     * - Use mobile & hasn't send yet
     * @param string $select
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function get_list_for_send($select = 'id, title', $limit = 10)
    {   
        // Get data last 7 days 
        $prev_day = date('Y-m-d H:i:s', strtotime('-7 days'));
        return self::get_list($select, 
            "((email_use = '1' AND email_sent = '0') OR (mobile_use = '1' AND mobile_sent = '0')) AND created_on >= '{$prev_day}'", 
            $limit
        );
    }

    /**
     * Get notification data for send
     *
     * @param int $id
     * @param string $select select columns
     * @param string $where condition
     * @return stdClass|NULL
     */
    public static function get_notification_for_send($id = 0, $select = '*', $where = '')
    {
        global $wpdb;
        $sql = "SELECT {$select} 
            FROM ".self::get_table()." 
            WHERE id = {$id} AND $where
            LIMIT 1
        ";
        return $wpdb->get_row($sql);
    }

    /**
     * Update sending status 
     *
     * @param int $id
     * @param array $data
     * @return int|false
     */
    public static function update_status($id = 0, $data = [])
    {
        global $wpdb;
        return $wpdb->update(self::get_table(), $data, ['id' => $id]);
    }
}