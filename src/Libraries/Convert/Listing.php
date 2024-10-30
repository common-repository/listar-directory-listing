<?php
namespace ListarWP\Plugin\Libraries\Convert;

use ListarWP\Plugin\Libraries\Convert_Abstract;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Place_Model;
use WP_Query;
use Exception;

class Listing extends Convert_Abstract {
    
    /**
     * Check post status
     * @var string 
     */
    static $post_status = '';

    /**
     * Check post exist
     * @var bool
     */
    static $post_exist = false;

    /**
     * Term index
     */
    static $term_index = [];

    /**
     * Import csv
     * @param string $term_name
     * @param string $file_uploaded
     * @return void
     */
    static function import($file_uploaded = NULL)
    {
        $delimiter = ',';
        $csv_data = [];
        
        // Social network fields 
        $social_network_fields = array_column(Place_Model::$social_network, 'field');      

        // Parse csv file 
        if(!file_exists($file_uploaded) || !is_readable($file_uploaded))
            throw __('Undefined upload file', 'listar');

        $header = NULL;        
        if (($handle = fopen($file_uploaded, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if(!$header) {
                    $header = $row;
                } else {
                    // If header and row data are not same > skip import
                    if(sizeof($header) !== sizeof($row)) {
                        continue;
                    }

                    $row_combine = array_combine($header, $row);

                    // Collect tags
                    $row_combine['tag_set'] = self::get_terms_data($row_combine, 'tag', 'post');

                    // Collect features
                    $row_combine['feature_set'] = self::get_terms_data($row_combine, 'feature', Listar::$post_type);

                    // Collect categories
                    $row_combine['category_set'] = self::get_terms_data($row_combine, 'category', Listar::$post_type);

                    // Collection location
                    $row_combine['location'] = implode(',',[$row_combine['country'], $row_combine['city'], $row_combine['state']]);
                    $row_combine['location_set'] = self::get_terms_data($row_combine, 'location', Listar::$post_type);

                    $csv_data[] = $row_combine;
                }
            }
            fclose($handle);
        }
        
        if(empty($csv_data)) {
            throw new Exception(__('Data uploaded is empty', 'listar'));            
        }

        self::$result['total'] = sizeof($csv_data);
        
        // Start to import
        foreach($csv_data as $index => $row) {
            $title = sanitize_text_field($row['title']);
            $post = NULL;

            if(self::$post_exist) {
                $post = post_exists($title, '', '', Listar::$post_type);
            }

            if(!$post) {
                $post = wp_insert_post([
                    'post_type' => Listar::$post_type,
                    'post_title' => $title,
                    'post_content' => sanitize_text_field($row['excerpt']),
                    'post_excerpt' => sanitize_text_field($row['excerpt']),
                    'post_status' => self::$post_status,
                    'tags_input' => sanitize_text_field($row['tag_set'])
                ], TRUE);
            } else {
                wp_update_post([
                    'ID' => $post,
                    'post_content' => sanitize_text_field($row['excerpt']),
                    'post_excerpt' => sanitize_text_field($row['excerpt']),
                    'tags_input' => sanitize_text_field($row['tag_set']),
                    'post_status' => self::$post_status
                ]);
            }

            if(is_wp_error($post)) {
                self::$errors[$title] = $post->get_error_message();
                self::$result['error']++;
            } else {
                self::$result['success']++;

                // Tag set
                if(!empty($row['tag_set'])) {
                    $tag_index = self::get_terms_index($row['tag_set'], 'tag');
                    wp_set_post_terms($post, $tag_index, 'post_tag');
                }

                // Feature set
                if(!empty($row['feature_set'])) {
                    $feature_index = self::get_terms_index($row['feature_set'], 'feature');
                    wp_set_post_terms($post, $feature_index, Listar::$post_type . '_feature');
                }

                // Category Set
                if(!empty($row['category_set'])) {
                    $category_index = self::get_terms_index($row['category_set'], 'category');
                    wp_set_post_terms($post, $category_index, Listar::$post_type . '_category');
                }

                // Location Set
                if(!empty($row['location_set'])) {
                    $location_index = self::get_terms_index($row['location_set'], 'location');
                    wp_set_post_terms($post, $location_index, Listar::$post_type . '_location');
                }

                // Location remake for set_metadata function
                $row = array_merge($row, [
                    'country' => isset(self::$term_index['location'][$row['country']]) ?
                        self::$term_index['location'][$row['country']] : 0,
                    'city' => isset(self::$term_index['location'][$row['city']]) ?
                        self::$term_index['location'][$row['city']] : 0,
                    'state' => isset(self::$term_index['location'][$row['state']]) ?
                        self::$term_index['location'][$row['state']] : 0,
                ]);

                // Featured image
                $img_url = trim(sanitize_url($row['image']));
                if(!empty($row['image'])) {
                    $img_id = self::download_image($post, $img_url);
                    if($img_id) {
                        // Set post thumb for post
                        set_post_thumbnail($post, $img_id);
                    }
                }

                /**
                 * Galleries
                 * - Explode image url to array
                 * - Check  internal > Get id
                 *          external > Download > Get id 
                 * - $row['galleries']: from data import
                 * - $row['gallery']: for update
                 */
                if(isset($row['galleries']) && !empty($row['galleries'])) {
                    $galleries = explode(',', trim(sanitize_text_field($row['galleries'])));
                    $galleries_ids = [];
                    if(!empty($galleries)) {
                        foreach($galleries as $img_url) {
                            $img_url = trim(sanitize_url($img_url));
                            $img_id = self::download_image($post, $img_url);
                            if($img_id) {
                                $galleries_ids[] = $img_id;
                            }
                        }

                        $row['gallery'] = implode(',', $galleries_ids);
                    }
                }

                // Social network
                $row['social_network'] = array_fill_keys($social_network_fields, "");
                foreach($row['social_network'] as $key => &$value) {
                    if(isset($row[$key]) && $row[$key] != '') {
                        $value = sanitize_url($row[$key]);
                    }    
                }
                
                //  Meta Insert
                Place_Model::set_metadata($post, $row);
            }
        }
    }

    /**
     * Export csv
     *
     * @return void
     */
    static function export()
    {
        $args = array_merge([
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
            'posts_per_page' => -1,
        ]);

        $query  = new WP_Query($args);
        $posts  = $query->get_posts();

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                Place_Model::assign_metadata($post);      
            }
        }
        
        if(!empty($posts)) {  
            // Social network fields 
            $social_network_fields = array_column(Place_Model::$social_network, 'field');      
            
            foreach($posts as $index => $post) {
                // Category
                $category_data = wp_get_post_terms($post->ID, 'listar_category', ['fields' => 'names']);
                $category_str = !empty($category_data) ? implode(',', $category_data) : '';

                // Features
                $feature_data = wp_get_post_terms($post->ID, 'listar_feature', ['fields' => 'names']);
                $feature_str = !empty($feature_data) ? implode(',', $feature_data) : '';

                // Tags
                $tag_data = wp_get_post_terms($post->ID, 'post_tag', ['fields' => 'names']);
                $tag_str = !empty($tag_data) ? implode(',', $tag_data) : '';

                // Image
                $image = get_the_post_thumbnail_url($post->ID);

                // Galleries
                $galleries = [];
                $galleries_str = '';
                if(!empty($post->galleries)) {
                    foreach($post->galleries as $gallery) {
                        $galleries[] = $gallery['full']['url'];      
                    }
                }
                $galleries_str = !empty($galleries) ? implode(',', $galleries) : ' ';
                
                $row_data = [
                    'title' => $post->post_title,
                    'excerpt' => $post->post_excerpt,                    
                    'country' => $post->country_name,		
                    'state' => $post->state_name,		
                    'city' => $post->city_name,		
                    'address' => $post->address,		
                    'zip_code' => $post->zip_code,		
                    'phone' => $post->phone,		
                    'fax' => $post->zip_fax,		
                    'email' => $post->email,		
                    'website' => $post->website,		
                    'status' => $post->status,		
                    'color' => $post->color,
                    'icon' => $post->icon,     
                    'date_establish' => $post->date_establish,		
                    'longitude' => $post->longitude,		
                    'latitude' => $post->latitude,		
                    'price_min' => $post->price_min,	
                    'price_max' => $post->price_max,	
                    'booking_price' =>  $post->booking_price,
                    'booking_style' => $post->booking_style,
                    'tag' => $tag_str, 	
                    'feature' => $feature_str,	
                    'category' => $category_str,      
                ];

                /**
                 * Social network 
                 * - Init columns with 1st row
                 * - Map data when the data is not empty
                 */
                if($index == 0) {
                    foreach($social_network_fields as $field) {
                        $row_data[$field] = ' ';
                    }
                }    
                
                if(!empty($post->social_network)) {
                    $row_data = array_merge($row_data, $post->social_network);
                }

                // Image & Galleries
                $row_data['image'] = $image;
                $row_data['galleries'] = $galleries_str;

                // Final row data   
                $csv_data[] = $row_data;  
            }

            $urlparts = wp_parse_url(home_url());
            $domain = $urlparts['host'];

            listar_array_to_csv_download($csv_data, $domain.'-'.date('Y-m-d H:s').'.csv');
        }
    }

    /**
     * Get term index for insert
     *
     * @param array $row_combine
     * @param string $taxonomy tag|feature|location
     * @param string $prefix empty|listar
     * @version 1.0.3
     * @return array
     */
    static function get_terms_data($row_combine = [], $taxonomy = '', $prefix = '') {
        if(isset($row_combine[$taxonomy])) {
            $result = [];
            $term_array = explode(',', $row_combine[$taxonomy]);
            if(!empty($term_array)) {
                foreach($term_array as $term) {
                    $term = trim(sanitize_text_field($term));
                    if($term) {
                        $result[] = $term;
                        // Create term index
                        if (!isset(self::$term_index[$taxonomy][$term])) {
                            $prefix_taxonomy = $prefix !== '' ? $prefix . '_' . $taxonomy : $taxonomy;

                            if (!$term_info = term_exists($term, $prefix_taxonomy)) {
                                $term_info = wp_insert_term($term, $prefix_taxonomy);
                            }

                            if (!is_wp_error($term_info)) {
                                self::$term_index[$taxonomy][$term] = absint($term_info['term_id']);
                            }
                        }
                    }
                }
            }
            return $result;
        }
        return [];
    }

    /**
     * Get term index by array term string
     * @param array $terms
     * @param string $taxonomy
     * @version 1.0.27
     * @return array
     */
    static function get_terms_index($terms = [], $taxonomy = '') {
        if(!empty($terms)) {
            $result = [];
            foreach($terms as $term) {
                if(isset(self::$term_index[$taxonomy][$term])) {
                    $result[] = self::$term_index[$taxonomy][$term];
                }
            }
            return $result;
        }
        return [];
    }
}