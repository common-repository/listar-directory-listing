<?php
namespace ListarWP\Plugin\Models;
use Exception;

class Job_Model {

    const STATUS_DONE = 'done';
	const STATUS_QUEUED = 'queued';
	const STATUS_RUNNING = 'running';
	const STATUS_FAILED = 'failed';

    /**
     * Notification table
     * @var string
     */
    public static $table_suffix = 'listar_jobs';

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
     * Insert jobs
     * 
     * @param array $data
     * @return int insert ID
     * @throws Exception
     */
    public static function insert($data = [])
    {
        // Insert new 
        global $wpdb;
        
        $data['created_on'] = gmdate('Y-m-d H:i:s');
        $wpdb->insert(self::get_table(), $data);

        if(!$wpdb->insert_id) {
            throw new Exception(__('Create notification error.'));
        }
        
        return $wpdb->insert_id;
    }

     /**
     * Get list
     *
     * @param string $select      
     * @param string $where
     * @param int $limit
     * @param string $order_by
     * @return array
     * @throws Exception
     */
    public static function get_list($select = '*', $where = '', $limit = 10, $order_by = '')
    {
        global $wpdb;
        $sql = "SELECT {$select} 
            FROM ".self::get_table()." 
            WHERE {$where}
            ORDER BY {$order_by}
            LIMIT {$limit}
        ";
        
        return $wpdb->get_results($sql);
    }

    /**
     * Get list run
     * - Since last 7 days
     * @param string $select      
     * @param int $limit
     * @param string $order_by
     * @return stdClass
     * @throws Exception
     */
    public static function get_jobs_for_run($select = '*', $limit = 1, $order_by = 'created_on asc')
    {   
        // Get data last 7 days 
        $prev_day = gmdate('Y-m-d H:i:s', strtotime('-7 days'));
        global $wpdb;
        $sql = "SELECT {$select} 
            FROM ".self::get_table()." 
            WHERE status = '".self::STATUS_QUEUED."' AND created_on >= '{$prev_day}' 
            ORDER BY {$order_by}
            LIMIT {$limit}
        ";
        
        return $wpdb->get_row($sql);
    }

    /**
     * Update stautus
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