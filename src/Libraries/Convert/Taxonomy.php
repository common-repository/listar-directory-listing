<?php
namespace ListarWP\Plugin\Libraries\Convert;

use Exception;
use ListarWP\Plugin\Libraries\Convert_Abstract;

class Taxonomy extends Convert_Abstract {

    /**
     * Term name
     */
    static $term_name = '';

     /**
     * Import csv
     * @param string $term_name
     * @param string $file_uploaded
     * @return void
     */
    static function import($term_name = '', $file_uploaded = NULL)
    {
        self::$term_name = $term_name;
        $delimiter = ',';
        $csv_data = [];

        // Parse csv file 
        if(!file_exists($file_uploaded) || !is_readable($file_uploaded))
            throw __('Undefined upload file', 'listar');

        $header = NULL;        
        if (($handle = fopen($file_uploaded, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if(!$header) {
                    $header = $row;
                } else {
                    $row_combine = array_combine($header, $row);
                    $csv_data[] = $row_combine;
                }
            }
            fclose($handle);
        }
        
        if(empty($csv_data)) {
            throw new Exception(__('Data uploaded is empty', 'listar'));            
        }

        self::$result['total'] = sizeof($csv_data);
        
        foreach($csv_data as $index => $row) {
            $term = get_term_by('name', sanitize_text_field($row['title'] ), self::$term_name);   
            if($term === false) {
                $insert_term = wp_insert_term(sanitize_text_field($row['title']) , self::$term_name, [
                    'description' => sanitize_text_field($row['description'])
                ]);
                if(!is_wp_error($insert_term)) {
                    $term_id = $insert_term['term_id'];
                }
            } else {
                $term_id = $term->term_id;
                wp_update_term($term_id, self::$term_name, [
                    'description' => sanitize_text_field($row['description'])
                ]);
            }
            update_term_meta($term_id, 'color', sanitize_text_field($row['color']));
            update_term_meta($term_id, 'icon', sanitize_text_field($row['icon']));

            // Featured image
            $img_url = trim(sanitize_text_field($row['image']));
            if(!empty($img_url)) {
                $img_id = self::download_image($term_id, $img_url);
                if($img_id) {
                    // set featured image
                    update_term_meta($term_id, 'featured_image', $img_id);
                }
            }

            self::$result['success']++;
        }

        // Update parent
        foreach($csv_data as $index => $row) {
            $term = get_term_by('name', sanitize_text_field($row['title'] ), self::$term_name);   
            if(!empty($term) && !empty($row['parent'])) {
                $parent = get_term_by('name', sanitize_text_field($row['parent'] ), self::$term_name);   
                wp_update_term($term->term_id, self::$term_name, [
                    'parent' => !empty($parent) ? $parent->term_id : 0
                ]); 
            }
        }
    }

    /**
     * Export csv
     * @param string $term_name
     * @return void
     */
    static function export($term_name = '')
    {
        $taxonomies = get_terms( sanitize_text_field($term_name), [
            'hide_empty' => 0
        ]);
        
        $csv_data = [];
        
        foreach($taxonomies as $term) {
            $icon = get_term_meta($term->term_id, 'icon', true);
            $color = get_term_meta($term->term_id, 'color', true);
            $featured_image = get_term_meta($term->term_id, 'featured_image', true);
            $image_url = '';
            if((int)$featured_image > 0) {
                $image_url = wp_get_attachment_url($featured_image);
            }

            $parent = NULL;
            $parent = get_term_by('id', $term->parent, $term_name);

            $csv_data[] = [
                'title' => $term->name,
                'description' => $term->description,
                'color' => $color,
                'icon' => $icon,
                'image' => $image_url,
                'parent' => !empty($parent) ? $parent->name : '',
            ];
        }

        $urlparts = wp_parse_url(home_url());
        $domain = $urlparts['host'];

        listar_array_to_csv_download($csv_data, $domain.'-'.$term_name.'-'.date('Y-m-d H:s').'.csv');
    }
}