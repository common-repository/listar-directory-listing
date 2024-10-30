<?php
namespace ListarWP\Plugin\Models;

use ListarWP\Plugin\Listar;
use WP_User;
use WP_Query;
use WP_Post;
use Exception;

class Place_Model {
    /**
     * Check is is logged in or not
     * - Check wishlist or condition related with authorize
     *@var WP_User
     */
    static $user = NULL;

    /**
     * Medata data get from db
     *
     * @var array
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static $metadata_post = [];

    /**
     * Define variables for metadata fields
     * - Default metadata
     * @var array
     */
    public static $metadata = [
        'country' => [ 'format' => 'integer', 'set_metadata' => FALSE],
        'state' => ['format' => 'integer', 'set_metadata' => FALSE],
        'city' => ['format' => 'integer', 'set_metadata' => FALSE],
        'address' => ['format' => 'text'],
        'zip_code' => ['format' => 'text'],
        'phone' => ['format' => 'text'],
        'whatsapp' => ['format' => 'text'],
        'fax' => ['format' => 'text'],
        'email' => ['format' => 'text'],
        'website' => ['format' => 'text'],
        'color' => ['format' => 'text'],
        'icon' => ['format' => 'text'],
        'status' => ['format' => 'text'],
        'date_establish' => ['format' => 'text'],
        'longitude' => ['format' => 'text'],
        'latitude' => ['format' => 'text'],
        'price_max' => ['format' => 'integer'],
        'price_min' => ['format' => 'integer'],
        'gallery' => ['format' => 'gallery'],
        'attachment' => ['format' => 'attachment'],
        'video_url' => ['format' => 'text'],
        'social_network' => ['format' => 'json'],
        'booking_style' => ['format' => 'text'],
        'booking_price' =>  ['format' => 'text'],
        'booking_disable' =>  ['format' => 'integer'],
        'claim_method_charge' =>  ['format' => 'text'],
        'claim_use' =>  ['format' => 'integer'],
        'claim_price' =>  ['format' => 'text']
    ];

    /**
     * Social network list
     *
     * @var array
     * @author Paul <paul.passionui@gmail.com>
     */
    public static $social_network = [
        [
            'label' => 'Facebook',
            'field' => 'facebook',
            'placeholder' => 'Facebook',
        ],
        [
            'label' => 'Twitter',
            'field' => 'twitter',
            'placeholder' => 'Twitter',
        ],
        [
            'label' => 'Google+',
            'field' => 'google_plus',
            'placeholder' => 'Google+',
        ],
        [
            'label' => 'Pinterest',
            'field' => 'pinterest',
            'placeholder' => 'Pinterest',
        ],
        [
            'label' => 'Tumblr',
            'field' => 'tumblr',
            'placeholder' => 'Tumblr',
        ],
        [
            'label' => 'Linkedin',
            'field' => 'linkedin',
            'placeholder' => 'Linkedin',
        ],
        [
            'label' => 'Youtube',
            'field' => 'youtube',
            'placeholder' => 'Youtube',
        ],
        [
            'label' => 'Instagram',
            'field' => 'instagram',
            'placeholder' => 'Instagram',
        ],
        [
            'label' => 'Flickr',
            'field' => 'flickr',
            'placeholder' => 'Flickr',
        ]
    ];

    /**
     * Open hour pattern
     *
     * @var string
     * @author Paul <paul.passionui@gmail.com>
     */
    static $opening_hour_pattern = "/^(opening_hour)\_([0-9]{1})\_([a-z]+)/";

    /**
     * Keys for filtering meta for list
     *
     * @since 1.0.0
     */
    static $metadata_list_keys = ['address', 'phone', 'status', 'longitude', 'latitude'];

    /**
     * Total records related get
     *
     * @var integer
     * @since 1.0.0
     */
    static $post_limit_related = 5;

    /**
     * Total records related get
     *
     * @var integer
     * @since 1.0.0
     */
    static $post_limit_recent = 5;

    /**
     * Get last data
     *
     * @param array $args WP_Query Arguments
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function get_list_data($args = []) {
        $args = array_merge([
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
            'posts_per_page' => self::$post_limit_recent,
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
     * Get related data
     *
     * @param array $args WP_Query Arguments
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function get_related_data($args = []) {
        $args = array_merge([
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => self::$post_limit_related,
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
     * Get last data
     *
     * @param array $args WP_Query Arguments
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function get_recent_data($args = []) {
        $args = array_merge([
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
            'posts_per_page' => self::$post_limit_recent,
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
     * Assign metadata
     * - WP form admin
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_metadata(&$post) {
        if(is_object($post) && $post->ID) {
            $metadata = get_post_meta($post->ID, '', TRUE);

            // Country/State/City
            if(isset($metadata['csc'])) {
                $csc = explode(',', $metadata['csc'][0]);
                if (!empty($csc)) {
                    $metadata['country'][0] = isset($csc[0]) ? $csc[0] : 0;
                    $metadata['state'][0] = isset($csc[1]) ? $csc[1] : 0;
                    $metadata['city'][0] = isset($csc[2]) ? $csc[2] : 0;

                    $post->country_name = '';
                    $post->city_name = '';
                    $post->state_name = '';

                    if (isset($csc[0])) {
                        $term = get_term($csc[0]);
                        $post->country_name = $term->name ?? '';
                    }

                    if (isset($csc[1])) {
                        $term = get_term($csc[1]);
                        $post->city_name = $term->name ?? '';
                    }

                    if (isset($csc[2])) {
                        $term = get_term($csc[2]);
                        $post->state_name = $term->name ?? '';
                    }
                }
            }

            // Gallery
            if(isset($metadata['gallery'])) {
                self::$metadata_post['gallery'] = $metadata['gallery'];
                self::assign_galleries($post);
            }

            // Attachment
            if(isset($metadata['attachment']) && !empty($metadata['attachment'])) {
                $attachment = listar_get_single_value($metadata['attachment']);
                if (!empty($attachment)) {
                    $ids = explode(',', $attachment);
                    $post->attachments = [];
                    foreach ($ids as $attachment_id) {
                        $attached_file = get_attached_file($attachment_id);
                        $post->attachments[] = [
                            'url' => wp_get_attachment_url($attachment_id),
                            'name' => basename($attached_file),
                            'size' => size_format(filesize($attached_file), 2)
                        ];
                    }
                } else {
                    $post->attachments = [];
                }
            }

            // Open Hours
            $post->opening_hour = [];

            foreach($metadata as $key => $value) {
                preg_match(self::$opening_hour_pattern, $key, $matches);
                if( $matches) {
                    $post->opening_hour[$matches[2]][$matches[3]] = $value;
                    unset($metadata[$key]);
                }
            }

            // Social Network
            $post->social_network = [];

            // Common fields
            if(is_array($metadata) && !empty($metadata)) {
                foreach(self::$metadata as $key => $value) {
                    if(isset($metadata[$key])) {
                        $metadata_single = listar_get_single_value($metadata[$key]);
                        switch($value['format']) {
                            case 'integer':
                                $post->{$key} = absint($metadata_single);
                                break;
                            case 'text':
                                $post->{$key} = esc_attr($metadata_single);
                                break;
                            case 'json':
                                $post->{$key} = json_decode(stripslashes($metadata_single), TRUE);
                                break;
                            case 'boolean':
                                $post->{$key} = $metadata_single === 'true' || $metadata_single === true;
                        }
                    } else {
                        $post->{$key} = NULL;
                    }
                }
            }

            // Booking use status
            self::assign_booking_status($post);
        }
    }

    /**
     * Set metadata
     * - WP admin form
     * - This function is using for handle WP admin submit form
     *
     * @param integer $post_id
     * @param array $_post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function set_metadata($post_id = 0, $_post = []) {
        foreach(self::$metadata as $key => $value) {
            if(isset($value['set_metadata']) && $value['set_metadata'] == FALSE) {
                continue;
            }

            if (array_key_exists($key, $_post)) {
                update_post_meta($post_id, $key, sanitize_text_field($_post[$key]));
            }
        }

        // Save term as customization
        $terms = [];
        if(isset($_post['country'])) {
            $terms[] = absint($_post['country']);
        } else {
            $terms[] = 0;
        }

        if(isset($_post['state'])) {
            $terms[] = absint($_post['state']);
        } else {
            $terms[] = 0;
        }

        if(isset($_post['city'])) {
            $terms[] = absint($_post['city']);
        } else {
            $terms[] = 0;
        }

        // Country/State/City: Meta data for location (just get for show)
        wp_set_post_terms( $post_id, $terms, Listar::$post_type.'_location');
        update_post_meta($post_id, 'csc', implode(',', $terms));

        /**
         * Save Opening Hours as metadata
         * - Each day may have more than 2 opening hours
         * - meta key prefix = opening_hour_{day_of_week}_{[start, end]}
         * -- day_of_week: use php date format date('w', strtotime($date));
         * -- [start, end]: 'start' for start time, 'end' for end time
         */
        if(isset($_post['opening_hour']) && !empty($_post['opening_hour'])) {
            foreach($_post['opening_hour'] as $day_of_week => $row) {
                $start_meta_key = implode('_', ['opening_hour', $day_of_week, 'start']);
                $end_meta_key   = implode('_', ['opening_hour', $day_of_week, 'end']);

                // Remove all meta key prefix by day
                delete_post_meta($post_id, $start_meta_key);
                delete_post_meta($post_id, $end_meta_key);

                // Add again of there have data
                foreach($row['start'] as $index => $time) {
                    if($time) {
                        $start_time     = sanitize_text_field($time);
                        $end_time       = isset($row['end'][$index]) ? $row['end'][$index] : 0;
                        add_post_meta($post_id, $start_meta_key, $start_time);
                        add_post_meta($post_id, $end_meta_key, $end_time);
                    }
                }
            }
        }

        if(isset($_post['social_network']) && is_array($_post['social_network']) && !empty($_post['social_network'])) {
            update_post_meta($post_id, 'social_network', sanitize_text_field(json_encode($_post['social_network'])));
        }
    }

    /**
     * Assign fields for view data
     * - Reset API single view
     *
     * @param WP_Post $post Post object.
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_data_view(&$post) {
        if(is_object($post) && $post->ID) {
            self::$metadata_post = get_post_meta($post->ID, '', TRUE);
        
            // Set props tag
            self::assign_tags($post);

            // Set prop author
            self::assign_author_data($post);

            // Set prop image
            self::assign_image($post);

            // Set prop category
            self::assign_taxonomy_category($post, TRUE, TRUE);
            self::assign_taxonomy_category($post, FALSE, FALSE);

            // Set prop feature
            self::assign_taxonomy_features($post, FALSE, TRUE);

            // Set prop rating
            self::assign_rating($post, TRUE);

            // Set prop gallery
            self::assign_galleries($post);

            // Set prob attachment
            self::assign_attachment($post);

            // Set prop wishlist
            self::assign_wishlist($post);

            /**
             * Set status base on open hours
             * - Optional 
             * @ticket LISTAR-294
             */
            if(Setting_Model::get_option('open_hour_status')) {                
                unset(self::$metadata_post['status']); // Make sure not assign by > Set prop for common fields
                $open_hour_status_open = Setting_Model::get_option('open_hour_status_open');
                $open_hour_status_close = Setting_Model::get_option('open_hour_status_close');
                $post->status = listar_get_opening_hour_status(self::$metadata_post, $open_hour_status_open, $open_hour_status_close);
            }

            // Set prop Opening Hours
            self::assign_open_hours($post);            

            // Set prop for country city/state/province
            self::assign_address_data($post);
            
            // Set prop for common fields
            if(is_array(self::$metadata_post) && !empty(self::$metadata_post)) {
                foreach(self::$metadata as $key => $value) {
                    if(isset(self::$metadata_post[$key])) {
                        $metadata_single = listar_get_single_value(self::$metadata_post[$key]);
                        switch($value['format']) {
                            case 'integer':
                                $post->{$key} = absint($metadata_single);
                                break;
                            case 'text':
                            case 'gallery':
                                $post->{$key} = esc_attr($metadata_single);
                                break;
                            case 'json':
                                $post->{$key} = json_decode(stripslashes($metadata_single), TRUE);
                                break;
                            case 'boolean':
                                $post->{$key} = $metadata_single === 'true' || $metadata_single === true;
                                break;
                        }
                    } else {
                        $post->{$key} = NULL;
                    }
                }
            }

            // Customize pricing format
            $post->price_max = Setting_Model::currency_format($post->price_max);
            $post->price_min = Setting_Model::currency_format($post->price_min);                   

            // Booking use status
            self::assign_booking_status($post);

            // Social network
            self::assign_social_network($post);

            // Set prop related
            $post->related = self::get_related_data([
                'tax_query' => [
                    [
                        'taxonomy' => Listar::$post_type.'_category',
                        'field'    => 'term_id',
                        'terms'    => $post->category->term_id
                    ]
                ],
                'post__not_in' => [$post->ID],
                'caller_get_posts'=> 1
            ]);

            // // Set prop recent data
            $post->lastest = self::get_recent_data([
                'post__not_in' => [$post->ID],
                'orderby' => 'rand'
            ]);
        }
    }

    /**
     * Assign data list with basic information
     * - Image, Rating, Category, Addr, Phone
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_data_list(&$post) {
        if(is_object($post) && $post->ID) {
            $metadata = get_post_meta($post->ID, '', FALSE);
            $metadata_filter = [];
            
            // Refactor item data from single to multiple
            if(is_array($metadata) && !empty($metadata)) {
                $metadata = listar_convert_single_value($metadata);
                // Filter again by keys
                $metadata_filter = array_filter($metadata, function($k) {
                    return in_array($k, self::$metadata_list_keys);
                }, ARRAY_FILTER_USE_KEY);
            }
            // Set extra props
            if(!empty($metadata_filter) && is_array($metadata_filter)) {
                foreach($metadata_filter as $key => $val) {
                    $post->{$key} = $val;
                }
            }

            // Change permanent link
            $post->guid = get_the_permalink($post->ID);

            // Set prop image
            self::assign_image($post);

            // Set prop category
            self::assign_taxonomy_category($post);

            // Set prop link
            self::assign_links($post);

            // Set prop rating
            self::assign_rating($post);

            // Set prop wishlist
            self::assign_wishlist($post);

            // Booking use status
            self::assign_booking_status($post);

            /**
             * Set status base on open hours
             * - Optional 
             * @ticket LISTAR-294
             */
            if(Setting_Model::get_option('open_hour_status')) {                
                $open_hour_status_open = Setting_Model::get_option('open_hour_status_open');
                $open_hour_status_close = Setting_Model::get_option('open_hour_status_close');
                $post->status = listar_get_opening_hour_status($metadata, $open_hour_status_open, $open_hour_status_close);
            }
        }
    }

    /**
     * Get & assign author information
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_author_data(&$post) {
        $author = get_userdata($post->post_author);
        if(is_object($author)) {
            $post->author = User_Model::refactor_user_data($author);
        } else {
            $post->author = [];
        }
    }

    /**
     * Assign gallery data
     *
     * @param WP_Post $post
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_galleries(&$post) {
        $post->galleries = [];
        $galleries_str_ids =  isset(self::$metadata_post['gallery'])
            ? listar_get_single_value(self::$metadata_post['gallery']) : [];
        if($galleries_str_ids) {
            $ids = explode(',', $galleries_str_ids);
            foreach ($ids as $attachment_id) {
                $attachment_id = absint($attachment_id);
                if($attachment_id) {
                    $post->galleries[] = listar_get_image($attachment_id);
                }
            }
        }
    }

    /**
     * Assign attachment data
     *
     * @param WP_Post $post
     * @return void
     * @version 1.0.13
     */
    public static function assign_attachment(&$post) {
        // Attachment
        $attachment_ids =  isset(self::$metadata_post['attachment'])
            ? listar_get_single_value(self::$metadata_post['attachment']) : [];
        if(!empty($attachment_ids)) {
            $ids = explode(',', $attachment_ids);
            $post->attachments = [];
            foreach ($ids as $attachment_id) {
                $attached_file = get_attached_file( $attachment_id );
                $post->attachments[] = [
                    'url' => wp_get_attachment_url($attachment_id),
                    'name' => basename($attached_file),
                    'size' => (string) size_format( filesize( $attached_file ), 2 )
                ];
            }
        } else {
            $post->attachments = [];
        }
    }

    /**
     * Get related taxonomy category data
     *
     * @param WP_Post $post
     * @param boolean $single [return single category or multiple]
     * @param boolean $metadata include meta data or not
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_taxonomy_category(&$post, $single = TRUE, $metadata = FALSE) {
        $taxonomies = wp_get_post_terms($post->ID, 'listar_category');

        if($single) {
            $post->category = !empty($taxonomies) ? $taxonomies[0] : null;
            if($post->category) {
                $post->category->name = htmlspecialchars_decode($post->category->name);
            }

            // Assign more meta data
            if($post->category && $metadata) {
                Category_Model::assign_metadata($post->category);
            }
        } else {
            $post->categories = !empty($taxonomies) ? $taxonomies : [];

            // Assign more meta data
            if(!empty($post->categories) && $metadata) {
                foreach($post->categories as &$category) {
                    Category_Model::assign_metadata($category);
                }
            }
        }
    }

    /**
     * Get related taxonomy features data
     *
     * @param WP_Post $post
     * @param boolean $single [return single feature or multiple]
     * @param boolean $metadata include meta data or not
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_taxonomy_features(&$post, $single = TRUE, $metadata = FALSE) {
        $taxonomies = wp_get_post_terms($post->ID, 'listar_feature');

        if($single) {
            $post->feature = !empty($taxonomies) ? $taxonomies[0] : [];

            // Assign more meta data
            if($post->feature && $metadata) {
                Feature_Model::assign_metadata($post->feature);
            }
        } else {
            $post->features = !empty($taxonomies) ? $taxonomies : [];

            // Assign more meta data
            if(!empty($post->features) && $metadata) {
                foreach($post->features as &$feature) {
                    Feature_Model::assign_metadata($feature);
                }
            }
        }
    }

    /**
     * Set prop image
     *
     * @param WP_Post $post
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_image(&$post) {

        $image_id = get_post_thumbnail_id($post->ID);
        $image_url = get_the_post_thumbnail_url($post->ID);

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
     * @since 1.0.0
     */
    public static function assign_links( &$post ) {
        $base = 'base';
        $links = [
            'self' => [
                'href' => rest_url( trailingslashit( $base ) . $post->ID ),
            ],
            'collection' => [
                'href' => rest_url( $base )
            ]
        ];

        $post->links = $links;

        return $links;
    }

    /**
     * Set prop rating
     *
     * @param WP_Post $post
     * @param boolean $meta [assign rating meta data]
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_rating(&$post, $meta= FALSE) {
        $rating_meta_data = Comment_Model::get_rating_meta($post->ID);
        $post->rating_avg = (float) $rating_meta_data['rating_avg'];
        $post->rating_count = $rating_meta_data['rating_count'];
        if($meta) {
            $post->rating_meta = $rating_meta_data['rating_meta'];
        }
    }

    /**
     * Set prop wishlist
     *
     * @param WP_Post $post
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function assign_wishlist(&$post) {
        if(self::$user && property_exists(self::$user, 'ID')) {
            $post->wishlist = Wishlist_Model::check_exist(self::$user->ID, $post->ID);
        } else {
            $post->wishlist = FALSE;
        }
    }

    /**
     * Assign Open Hours meta data
     * @param WP_Post $post
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.1
     */
    public static function assign_open_hours(&$post) {
        $opening_hour_data = [];
        foreach(self::$metadata_post as $key => $value) {
            preg_match(self::$opening_hour_pattern, $key, $matches);
            if( $matches) {
                $opening_hour_data[$matches[2]][$matches[3]] = $value;
                unset(self::$metadata_post[$key]);
            }
        }
        
        $day_of_weeks = listar_get_open_hours_label();
        foreach($day_of_weeks as &$row) {
            if(isset($opening_hour_data[$row['day_of_week']])) {
                $start_data = isset($opening_hour_data[$row['day_of_week']]['start']) ? $opening_hour_data[$row['day_of_week']]['start'] : [];
                $end_data = isset($opening_hour_data[$row['day_of_week']]['end']) ? $opening_hour_data[$row['day_of_week']]['end'] : [];

                if(!empty($start_data)) {
                    foreach($start_data as $key => $time) {
                        $row['schedule'][] = [
                            'start' => $time,
                            'end' => $end_data[$key],
                        ];
                    }
                }
            } else {
                $row['schedule'][] = [
                    'start' => Setting_Model::get_option('time_min'),
                    'end' => Setting_Model::get_option('time_max'),
                ];
            }
        }

        // Set final data
        $post->opening_hour = $day_of_weeks;
    }

    /**
     * Assign Social network
     * @param WP_Post $post
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.20
     */
    public static function assign_social_network(&$post) {
        if(!empty($post->social_network)) {
            $column_check = array_column(self::$social_network, 'field');
            foreach($post->social_network as $key => $value) {
                if(!in_array($key, $column_check) || !$value) {
                    unset($post->social_network[$key]);
                }
            }
        }
    }

    /**
     * Set city/state/province name
     * @param WP_Post $post
     * @version 1.0.3
     */
    public static function assign_address_data(&$post) {
        $metadata = get_post_meta($post->ID, 'csc', TRUE);
        $csc = explode(',', $metadata);
        $post->location = [
            'country' => ['id' => 0, 'name' => ''],
            'city' => ['id' => 0, 'name' => ''],
            'state' => ['id' => 0, 'name' => '']
        ];

        if(!empty($csc)) {
            $post->country_name = '';
            $post->city_name = '';
            $post->state_name = '';

            if(isset($csc[0])) {
                $term = get_term($csc[0]);
                $post->country_name = $term->name ?? '';
                $post->location['country']['id'] = absint($csc[0]);
                $post->location['country']['name'] = $post->country_name;
            }

            if(isset($csc[1])) {
                $term = get_term($csc[1]);
                $post->state_name = $term->name ?? '';
                $post->location['state']['id'] = absint($csc[1]);
                $post->location['state']['name'] = $post->state_name;
            }

            if(isset($csc[2])) {
                $term = get_term($csc[2]);
                $post->city_name = $term->name ?? '';
                $post->location['city']['id'] = absint($csc[2]);
                $post->location['city']['name'] = $post->city_name;
            }
        }

        // Full address
        if(!property_exists($post, 'address')) {
            $post->address = get_post_meta($post->ID, 'address', true);
        }

        $post->full_address = $post->address;
        if($post->city_name) {
            $post->full_address .= ' '.$post->city_name;
        }

        if($post->state_name) {
            $post->full_address .= ', '.$post->state_name;
        }

        if(!property_exists($post, 'zip_code')) {
            $post->zip_code = get_post_meta($post->ID, 'zip_code', true);
            $post->full_address .= ' '.$post->zip_code;
        }

        if($post->country_name) {
            $post->full_address .= ' '.$post->country_name;
        }
        
    }

    /**
     * Get tags
     * @param WP_Post $post
     * @version 1.0.9
     */
    public static function assign_tags(&$post) {
        $data = get_the_tags($post->ID);
        $result = [];
        if(!empty($data)) {
            foreach ($data as $item) {
                $result[] = [
                    'id' => $item->term_id,
                    'name' => $item->name,
                    'slug' => $item->slug
                ];
            }
        }
        $post->tags = $result;
    }

    /**
     * Set booking status by setting default
     * @param $post
     */
    public static function assign_booking_status(&$post) {
        $price = get_post_meta($post->ID, 'booking_price', TRUE);
        $post->booking_use = Setting_Model::booking_use();
        if($post->booking_use && $post->booking_disable) {
            $post->booking_use = false;
        }
        $post->booking_style = get_post_meta($post->ID, 'booking_style', TRUE);
        if((int) $price > 0) {
            $post->booking_price = $price;
            $post->booking_price_display = Setting_Model::currency_format($price);
        } else {
            $post->booking_price = '';
            $post->booking_price_display = '';
        }
    }

    /**
     * Set random booking data
     * @since 1.0.13
     */
    public static function set_random_booking_data()
    {
        // Media
        $query = new WP_Query([
            'post_type'=>'listar',
            'posts_per_page' => -1
        ]);
        $rows = $query->get_posts();
        foreach($rows as $row) {
            $prices = [39, 49, 59, 69, 79];
            $booking_styles = ['standard', 'slot', 'daily', 'hourly'];

            $booking_price = $prices[array_rand($prices)];
            $booking_style = $booking_styles[array_rand($booking_styles)];

            update_post_meta($row->ID, 'booking_price', $booking_price);
            update_post_meta($row->ID, 'booking_style', $booking_style);
        }
    }

    /**
     * Check listing has claimed by someone
     * @param int $post_id WP_Post.ID 
     * @version 1.0.31
     * @return boolean
     */
    public static function is_claimed($post_id = 0) 
    {
        return get_post_meta($post_id, 'claim_use', true) == 1; 
    }
    
    /**
     * Get method of charge
     *
     * @param integer $post_id
     * @return string
     */
    public static function get_claim_method_charge($post_id = 0)
    {
        $claim_method_charge = get_post_meta($post_id, 'claim_method_charge', TRUE);
        if(!$claim_method_charge) {
            $claim_method_charge = Setting_Model::get_option('claim_method_charge');
        }

        if(empty($claim_method_charge)) {
            throw new Exception(__('Undefined the method of charge'));
        }

        return $claim_method_charge;
    }

    /**
     * Get claim price
     *
     * @param integer $post_id
     * @return int|double
     */
    public static function get_claim_charge_fee($post_id = 0)
    {
        $claim_price = get_post_meta($post_id, 'claim_price', TRUE);
        if(!$claim_price) {
            $claim_price = Setting_Model::get_option('claim_price');
        }

        return $claim_price;
    }
}
