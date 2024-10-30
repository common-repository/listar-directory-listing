<?php
namespace ListarWP\Plugin\Libraries;

abstract class Convert_Abstract {

     /**
     * Error handler
     * @var array
     */
    static $errors = [];

    /**
     * Import status
     * @var array
     */
    static $result = [
        'total' => 0, // total rows
        'success' => 0, // insert successfully
        'error' => 0, // insert error
    ];

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

    static abstract function import();

    static abstract function export();

    /**
     * Check image
     * - External > Download
     * - Internal > Move to upload folder
     *
     * @param int $post_id
     * @param string $url
     * @param string $active_tab
     * @version 1.0.27
     * @return int|WP_Error|null
     */
    static function download_image($post_id = 0, $url = '') {
        $scheme = parse_url($url);
        $check_file_type = wp_check_filetype(basename($url), self::$mimes);

        // Check if already exist on media/attachemt 
        $img_id = attachment_url_to_postid($url);
        if($img_id > 0) {
            return $img_id;
        }

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

                        // Download and insert image ok
                        return $img_id;
                    }
                } else {
                    if(is_wp_error($temp_file)) {
                        self::$errors[$post_id] = '';
                        foreach($temp_file->errors as $errors) {
                            foreach($errors as $key => $error) {
                                self::$errors[$post_id] .= $error .' ('.$url.')';
                            }
                        }
                    } else {
                        @unlink($temp_file);
                        self::$errors[$post_id] = __('Download image error. URL:'.$url);
                    }
                }
            }
        } else {
            self::$errors[$post_id] = __('Invalid image. URL:'.$url);
        }

        return NULL;
    }
}