<?php
namespace ListarWP\Plugin\Commands;

use ListarWP\Plugin\Libraries\Command_Interface;
use Exception;

class Taxonomy_Command implements Command_Interface {

    protected $environment;

    /**
     * Mimes for validation
     * Image only
     * @var array
     */
    static $mimes = [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'tiff|tif' => 'image/tiff'
    ];

    /**
     * Error handler
     */
    static $errors = [];

    /**
     * Command name
     * @return string
     */
    public static function command_name()
    {
        return 'listar-taxonomy';    
    }

    public function __construct( ) {
        $this->environment = wp_get_environment_type();
    }

    /**
     * Import csv file
     */
    public function import($args,  $assoc_args)
    {       
        try {
            $args = wp_parse_args(
                $assoc_args,
                array(
                    'file' => '',
                    'format'  => 'csv',
                    'type' => 'listar_category'
                )
            );

            $csv_data = listar_csv_to_array($args['file']);
            $type = $args['type'];

            foreach($csv_data as $index => $row) {
                $term = get_term_by('name', sanitize_text_field($row['title'] ), $type);   
                if($term === false) {
                    $insert_term = wp_insert_term(sanitize_text_field($row['title']) , $type, [
                        'description' => sanitize_text_field($row['description'])
                    ]);
                    if(!is_wp_error($insert_term)) {
                        $term_id = $insert_term['term_id'];
                    }
                } else {
                    $term_id = $term->term_id;
                    wp_update_term($term_id, $type, [
                        'description' => sanitize_text_field($row['description'])
                    ]);
                }
                update_term_meta($term_id, 'color', sanitize_text_field($row['color']));
                update_term_meta($term_id, 'icon', sanitize_text_field($row['icon']));

                // Featured image
                if(!empty($row['image'])) {
                    $this->_download_feature_image($term_id, trim(sanitize_text_field($row['image'])), 'taxonomy');
                }
            }

            // Update parent
            foreach($csv_data as $index => $row) {
                $term = get_term_by('name', sanitize_text_field($row['title'] ), $type);   
                if(!empty($term) && !empty($row['parent'])) {
                    $parent = get_term_by('name', sanitize_text_field($row['parent'] ), $type);   
                    wp_update_term($term->term_id, $type, [
                        'parent' => !empty($parent) ? $parent->term_id : 0
                    ]); 
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            debug(self::$errors);
        }
    }

    /**
     * Check image
     * - External > Download
     * - Internal > Move to upload folder
     *
     * @param int $post_id
     * @param string $url
     * @version 1.0.26
     * @return int|WP_Error|null
     */
    private function _download_feature_image($post_id = 0, $url = '') {
        $scheme = parse_url($url);
        $check_file_type = wp_check_filetype(basename($url), self::$mimes);

        // Validate file type
        if(!empty($check_file_type['type'])) {
            // External link
            if (!empty($scheme['host'])) {
                $temp_file = download_url($url);
                if (!is_wp_error($temp_file)) {
                    // Format for insert attachment
                    $file = [
                        'name' => basename($url),
                        'type' => 'image/png',
                        'tmp_name' => $temp_file,
                        'error' => 0,
                        'size' => filesize($temp_file),
                    ];

                    $result = wp_handle_sideload($file, [
                        'test_form' => FALSE,
                    ]);

                    if (!empty($result['error'])) {
                        self::$errors[$post_id] = $result['error'].__('URL:'.$url);
                    } else {
                        $filename = $result['file'];
                        $attachment = array(
                            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                            'post_mime_type' => $result['type'],
                            'post_status' => 'inherit',
                            'post_content' => ''
                        );

                        // Insert attachment file
                        $img_id = wp_insert_attachment($attachment, $filename);

                        // Generate thumbs
                        $image = get_post($img_id);
                        $full_size_path = get_attached_file($image->ID);
                        $attach_data = wp_generate_attachment_metadata($img_id, $full_size_path);
                        // Set meta data image
                        wp_update_attachment_metadata($img_id, $attach_data);

                        update_term_meta($post_id, 'featured_image', $img_id);

                        return $img_id;
                    }
                } else {
                    self::$errors[$post_id] = __('Download image error. URL:'.$url);
                }
            }
        } else {
            self::$errors[$post_id] = __('Invalid image. URL:'.$url);
        }

        return NULL;
    }
}