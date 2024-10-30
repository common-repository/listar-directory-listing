<?php
namespace ListarWP\Plugin\Models;

use WP_Term;
use ListarWP\Plugin\Listar;

class Category_Model {
    /**
     * Define variables for metadata fields
     * @var array
     */
    public static $metadata = [
        'featured_image' => [
            'format' => 'text'
        ],
        'icon' => [
            'format' => 'text'
        ],
        'color' => [
            'format' => 'text'
        ]
    ];

    /**
     * Import csv
     *
     * @return void
     */
    static function import()
    {
        
    }

    /**
     * Export csv
     *
     * @return void
     */
    static function export()
    {
        
    }
    
    /**
     * assign metadata
     *
     * @param WP_Term $term
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function assign_metadata(&$term) {
        if(is_object($term) && $term->term_id) {
            $metadata = get_term_meta($term->term_id, '', TRUE);

            // Common fields
            if(is_array($metadata) && !empty($metadata)) {
                $metadata = listar_convert_single_value($metadata);

                foreach(self::$metadata as $key => $value) {
                    if(isset($metadata[$key])) {
                        switch($value['format']) {
                            case 'integer':
                                $term->{$key} = absint($metadata[$key]);
                                break;
                            case 'text':
                                $term->{$key} = esc_attr($metadata[$key]);
                                break;
                            case 'json':
                                $term->{$key} = json_decode(stripslashes($metadata[$key]));
                        }

                        // Image sizes
                        if($key == 'featured_image') {
                            $term->image = listar_get_image($term->featured_image);
                        }
                    } else {
                        $term->{$key} = NULL;
                    }
                }
            } else {
                foreach(self::$metadata as $key => $value) {
                    $term->{$key} = NULL;
                }
            }

            // Count sub children
            $total_childs = wp_count_terms( Listar::$post_type.'_category', array(
                'hide_empty'=> 0,
                'parent'    => $term->term_id
            ));

            if($total_childs > 0) {
                $term->total_childs = (int)$total_childs;
                $term->has_child = true;
            } else {
                $term->total_childs = 0;
                $term->has_child = false;
            }
        }
    }

    /**
     * Set metadata
     *
     * @param int $term_id
     * @param array $_post
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function set_metadata($term_id = 0, $_post = []) {
        foreach(self::$metadata as $key => $value) {
            if (array_key_exists($key, $_post)) {
                update_term_meta($term_id, $key, sanitize_text_field($_post[$key]));
            }
        }
    }

    /**
     * Get mobile location menu
     *
     * @param string $location menu setting id
     * @param array $args params setting get
     * @return array
     * @since 1.0.2
     */
    public static function get_mobile_menu($location = '', $args = []) {
        $result = [];
        $items = get_nav_menu_items_by_location($location, $args = []);

        if(!empty($items)) {
            foreach($items as $item) {
                if($item->object === Listar::$post_type.'_category') {
                    $term = (object) [
                        'term_id' => (int)$item->object_id,
                        'name' => htmlspecialchars_decode($item->title),
                        'term_taxonomy_id' => (int)$item->object_id,
                        'taxonomy' => $item->object,
                        'description' => $item->description,
                        'url' => $item->url
                    ];

                    self::assign_metadata($term);

                    $result[] = $term;
                }
            }
        }
        return $result;
    }
}
