<?php
namespace ListarWP\Plugin\Models;
use Exception;
use ListarWP\Plugin\Listar;

class Banner_Model {
    /**
     * Set data include meta data
     * @var array
     */
    public $data = [];

    public function __construct()
    {
    }

    /**
     * return post type
     * @return string
     */
    public static function post_type()
    {
        return Listar::$post_type.'_banner';
    }

    /**
     * Assign meta data
     * @param \WP_Post $post_id
     */
    public static function assign_meta_data(&$post)
    {
        // Booking meta data
        $meta_data = get_post_meta($post->ID);
        if(!empty($meta_data)) {
            foreach ($meta_data as $key => $value) {
                $post->{$key} = listar_get_single_value($meta_data[$key]);
            }
        }
    }
}
