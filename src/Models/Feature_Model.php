<?php
namespace ListarWP\Plugin\Models;
use WP_Term;

class Feature_Model {
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
                        if($key == 'featured_image' && $term->featured_image) {
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
}
