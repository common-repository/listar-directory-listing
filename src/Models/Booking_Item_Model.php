<?php
namespace ListarWP\Plugin\Models;
use Exception;

class Booking_Item_Model {

    /**
     * table booking item
     * @var string
     */
    public static $table_suffix = 'listar_booking_items';

    /**
     * table meta data
     * @var string
     */
    public static $table_meta_suffix = 'listar_booking_itemmeta';

    /**
     * item type
     * @var string
     */
    public static $item_type = 'line_item';

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
     * Get table mata name
     * @return string
     */
    public static function get_table_meta()
    {
        global $wpdb;
        return $wpdb->prefix.self::$table_meta_suffix;
    }

    /**
     * Insert booking items & meta data
     * @param int $booking_id
     * @param string $name
     * @param array $meta_data
     * @throws Exception
     */
    public static function insert($booking_id = 0, $name = '', $meta_data = [])
    {
        global $wpdb;
        $wpdb->insert(self::get_table(), [
            'booking_item_name' => $name,
            'booking_item_type' => self::$item_type,
            'booking_id' => $booking_id,
        ]);

        if($wpdb->insert_id) {
            self::insert_medata_data($wpdb->insert_id, $meta_data);
        } else {
            throw new Exception(__('Can not create booking items'));
        }
    }

    /**
     * Delete booking item & meta
     * - Delete item
     * - Delete item meta
     * @param int $booking_id
     */
    public static function delete($booking_id = 0)
    {
        global $wpdb;

        $sql = "SELECT booking_item_id FROM ".self::get_table()." WHERE booking_id = {$booking_id}";
        $result = $wpdb->get_results($sql, ARRAY_A);

        if(!empty($result)) {
            $booking_item_ids = array_column($result, 'booking_item_id');
            $ids = implode( ',', array_map( 'absint', $booking_item_ids ) );
            $wpdb->query( "DELETE FROM ".self::get_table_meta()." WHERE booking_item_id IN($ids)");
        }

        $wpdb->delete(self::get_table(), [
            'booking_id' => $booking_id
        ]);
    }

    /**
     * Insert meta data
     * @param int $booking_item_id
     * @param array $meta_data
     */
    public static function insert_medata_data($booking_item_id = 0, $meta_data = [])
    {
        global $wpdb;
        if(!empty($meta_data)) {
            foreach($meta_data as $key => $value) {
                $wpdb->insert(self::get_table_meta(), [
                    'booking_item_id' => $booking_item_id,
                    'meta_key' => $key,
                    'meta_value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }
    }

    /**
     * Get list booking items
     * @param int $booking_id
     * @return array|object|null
     */
    public static function get_booking_item($booking_id = 0)
    {
        global $wpdb;
        $sql = "SELECT booking_item_id, booking_item_name FROM ".self::get_table()." WHERE booking_id = {$booking_id}";
        $result = $wpdb->get_results($sql, ARRAY_A);
        $booking_item_index = [];

        if(!empty($result)) {
            $booking_item_ids = array_column($result, 'booking_item_id');

            $sql = "SELECT * FROM " . self::get_table_meta() . " WHERE booking_item_id IN (".implode(',', $booking_item_ids).")";
            $result_sub = $wpdb->get_results($sql, ARRAY_A);
            if(!empty($result_sub)) {
                foreach($result_sub as $item) {
                    $booking_item_index[$item['booking_item_id']][] = $item;
                }
            }

            foreach ($result as &$item) {
                if(isset($booking_item_index[$item['booking_item_id']])) {
                    foreach($booking_item_index[$item['booking_item_id']] as $meta) {
                        $item[$meta['meta_key']] = $meta['meta_value'];
                    }
                }
            }
        }

        return $result;
    }
}
