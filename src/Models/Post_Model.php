<?php
namespace ListarWP\Plugin\Models;
use ListarWP\Plugin\Listar;
use WP_Query;
use WP_Post;

class Post_Model {
    /**
     * Define variables for metadata fields
     * @var array
     */
    public static $metadata = [
        'color' => array(
            'format' => 'text'
        ),
        'icon' => array(
            'format' => 'text'
        )
    ];

    /**
     * Total records related get
     * 
     * @var integer
     * @since 1.0.26
     */
    static $post_limit_related = 5;

    /**
     * Total records related get
     * 
     * @var integer
     * @since 1.0.26
     */
    static $post_limit_recent = 5;

    /**
     * @var string 
     */
    static $post_type = 'post';

    /**
     * Get related data
     *
     * @param array $args WP_Query Arguments
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public static function get_related_data($args = array()) {
        $args = array_merge([
            'ignore_sticky_posts' => 1,
            'post_type' => self::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => self::$post_limit_related,
            'fields' => ['ID', 'post_title', 'guid', 'category', 'post_date', 'post_author']
        ], $args);
        
        $query  = new WP_Query($args);
        $posts  = $query->get_posts();

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                self::assign_data_list($post);                
            }
        }  
        
        return $posts;
    }

    /**
     * Get lastest data
     *
     * @param array $args WP_Query Arguments
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public static function get_recent_data($args = array()) {
        $args = array_merge([
            'ignore_sticky_posts' => 1,
            'post_type' => self::$post_type,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => self::$post_limit_recent,
            'fields' => ['ID', 'post_title', 'guid', 'category', 'post_date', 'post_author']
        ], $args);
        
        $query  = new WP_Query($args);
        $posts  = $query->get_posts();

        // Fields filter
        $fields = isset($args['fields']) ? $args['fields'] : [];

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                
                // Fillter by key
                if(!empty($fields)) {
                    $post = (object) array_intersect_key((array)$post,array_flip($fields));
                }
                self::assign_data_list($post);                
            }
        }  
        
        return $posts;
    }
    
    /**
     * Assign metadata
     * - WP form admin
     * 
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26
     */
    public static function assign_metadata(&$post) {
        if(is_object($post) && $post->ID) {
            $metadata = get_post_meta($post->ID, '', TRUE);  
            
            // Common fields
            if(is_array($metadata) && !empty($metadata)) {
                foreach(self::$metadata as $key => $value) {
                    if(isset($metadata[$key][0])) {
                        switch($value['format']) {
                            case 'integer':
                                $post->{$key} = absint($metadata[$key][0]);
                                break;
                            case 'text':
                                $post->{$key} = esc_attr($metadata[$key][0]);
                                break;     
                            case 'json':
                                $post->{$key} = json_decode(stripslashes($metadata[$key][0]));
                        }
                    } else {
                        $post->{$key} = NULL;
                    }                    
                }
            }
        }        
    }

    /**
     * Set metadata
     * - WP admin form
     * - This function is using for handle WP amdin submit form
     * 
     * @param integer $post_id
     * @param array $_post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26 
     */
    public static function set_metadata($post_id = 0, $_post = []) { 
        foreach(self::$metadata as $key => $value) {   
            if(isset($value['set_metadata']) && $value['set_metadata'] == FALSE) {
                continue;
            }         

            if (array_key_exists($key, $_post)) {
                update_post_meta($post_id, $key, $_post[$key]);
            }
        }
    }

    /**
     * Assign fields for view data
     *
     * @param WP_Post $post Post object.
     * @param boolean $related related posts 
     * @param boolean $lastest lastest posts 
     * @return array  post detail
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26 
     */
    public static function assign_data_view(&$post, $related = false, $lastest = false) {
        if(is_object($post) && $post->ID) {
            $metadata = get_post_meta($post->ID, '', TRUE);                      
            
            // Set prop author
            self::assign_author_data($post);

            // Set prop image
            self::assign_image($post);
            
            // Set prop category
            self::assign_taxonomy_category($post, false);

            // Set prop for common fields
            if(is_array($metadata) && !empty($metadata)) {
                foreach(self::$metadata as $key => $value) {
                    if(isset($metadata[$key][0])) {
                        switch($value['format']) {
                            case 'integer':
                                $post->{$key} = absint($metadata[$key][0]);
                                break;
                            case 'text':
                                $post->{$key} = esc_attr($metadata[$key][0]);
                                break;     
                            case 'json':
                                $post->{$key} = json_decode(stripslashes($metadata[$key][0]));
                        }
                    } else {
                        $post->{$key} = NULL;
                    }                    
                }                
            }                    

            // Set prop related
            $post->related = [];
            if($related) {
                $post->related = self::get_related_data();
            }

            // Set prop recent data
            $post->lastest = [];
            if($lastest) {
                $post->lastest = self::get_recent_data([
                    'post__not_in' => [$post->ID]
                ]);           
            } 
        }
    }
    
    /**
     * Assign data list with basic information
     * - Image, Rating, Category
     * 
     * @param WP_Post $post
     * @param boolean $single [return single category or multiple]
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26
     */

    public static function assign_data_list(&$post) {
        if(is_object($post) && $post->ID) {
            $metadata = get_post_meta($post->ID, '', TRUE);                                  
            // Set extra props
            if(!empty($metadata) && is_array($metadata)) {
                foreach($metadata as $key => $val) {
                    $post->{$key} = $val;
                }
            }

            // Set prop author
            self::assign_author_data($post);

            // Set prop image
            self::assign_image($post);
            
            // Set prop category
            self::assign_taxonomy_category($post, false);

            // Set prop link   
            self::assign_links($post);
        }
    }

    /**
     * Get & assign author information
     * 
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26
     */
    public static function assign_author_data(&$post) {
        $author = get_userdata($post->post_author);
        if(is_object($author)) {
            $post->author = [
                'id' => $author->data->ID,
                'name' => $author->data->display_name,
                'email' => $author->data->user_email,
                'url' => $author->data->user_url,
                'image' => get_avatar_url($author->data->ID)
            ];
        } else {
            $post->author = [];
        }
    }

    /**
     * Get related taxonomy category data
     * 
     * @param WP_Post $post
     * @param boolean $single [return single category or multiple]
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26
     */
    public static function assign_taxonomy_category(&$post, $single = TRUE) {
        $taxonomies = wp_get_post_terms($post->ID, 'category');

        if($single) {
            $post->category = !empty($taxonomies) ? $taxonomies[0] : [];
        } else {
            $post->categories = !empty($taxonomies) ? $taxonomies : [];
        }
    }

    /**
     * Set prop image
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.26
     */
    public static function assign_image(&$post) {
        $image_id = get_post_thumbnail_id($post->ID);
        $image_url =  get_the_post_thumbnail_url($post->ID);

        if((int) $image_id > 0 && $image_url) {
            $post->image = [
                'id' => get_post_thumbnail_id($post->ID),
                'full' => ['url' => $image_url],
                'medium' => ['url' => get_the_post_thumbnail_url($post->ID, 'medium')],
                'thumb' => ['url' => get_the_post_thumbnail_url($post->ID, 'thumb')],
            ];
        } else {
            $post->image = Setting_Model::default_image();
        }
    }

    /**
     * Prepares links for the request.
     * - Set props for object param
     * 
     * @param WP_Post $post Post object.
     * @return array Links for the given post.
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.26
     */
    public static function assign_links( &$post ) {
        $base = 'base'; //sprintf( '%s/%s', $this->namespace, $this->rest_base );

        $links = array(
            'self'       => array(  
                'href' => rest_url( trailingslashit( $base ) . $post->ID ),
            ),
            'collection' => array(
                'href' => rest_url( $base ),
            ),  
        );

        $post->links = $links;

        return $links;
    }
}