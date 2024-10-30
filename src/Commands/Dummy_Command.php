<?php
namespace ListarWP\Plugin\Commands;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Command_Interface;
use ListarWP\Plugin\Models\Place_Model;
use ListarWP\Plugin\Models\User_Model;
use ListarWP\Plugin\Models\Comment_Model;
use Exception;
use WP_Query;

class Dummy_Command implements Command_Interface {

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
     * Command name
     * @return string
     */
    public static function command_name()
    {
        return 'listar-dummy';    
    }

    public function __construct( ) {
        $this->environment = wp_get_environment_type();
    }

    /**
     * Mixing the data
     */
    public function run()
    {   
        // Booking
        $types = ['standard', 'slot', 'hourly', 'daily'];
        $prices = [39, 49, 59, 69];

        // Social Network
        $social_network = [
            'facebook' => 'https://www.facebook.com/passionui',
            'twitter' => 'https://www.facebook.com/passionui',
            'google_plus' => 'https://codecanyon.net/user/passionui/portfolio',
            'pinterest' => 'https://www.facebook.com/passionui',
            'tumblr' => 'https://www.facebook.com/passionui',
            'linkedin' => 'https://www.facebook.com/passionui',
            'youtube' => 'https://www.youtube.com/channel/UCt_7rXE3zgj_a_UbGCFUz6Q',
            'flickr' => 'https://www.facebook.com/passionui'
        ];
        $exclude_media_ids = [];

        // User
        $users = get_users(['fields' => ['display_name', 'user_email', 'ID']]);
        $user_index = [];
        foreach($users as $row) {
            $media_id = get_user_meta($row->ID, User_Model::$user_photo_key, true);
            if($media_id) {
                $exclude_media_ids[] = $media_id;
            }    
            $user_index[] = $row->ID;           
        }

        // Tags
        $tags = get_terms('post_tag', [
            'parent' => 0,
            'hide_empty' => 0,
        ]);
        $tags_length = sizeof($tags);
        $tags_ids = array_column($tags, 'term_id');

        // Categories
        $categories = get_terms( Listar::$post_type.'_category', [
            'hide_empty' => 0
        ]);

        $category_index = [];
        foreach($categories as $row) {
            $media_id = get_term_meta($row->term_id, 'featured_image', TRUE);
            if($media_id) {
                $exclude_media_ids[] = $media_id;
            }    
            $category_index[$row->parent][] = $row->term_id;
        }

        // Features
        $features = get_terms( Listar::$post_type.'_feature', [
            'parent' => 0,
            'hide_empty' => 0
        ]);
        $feature_length = sizeof($features);
        $features_ids = array_column($features, 'term_id');

        // Location
        $locations = get_terms( Listar::$post_type.'_location', [
            'hide_empty' => 0
        ]);

        $locations_top_group = [];
        $locations_child_group = [];
        foreach($locations as $item) {
            $media_id = get_term_meta($item->term_id, 'featured_image', TRUE);
            if($media_id) {
                $exclude_media_ids[] = $media_id;
            }  
            $locations_child_group[$item->parent][] = $item->term_id;
            if($item->parent == 0) {
                $locations_top_group[] = $item->term_id;
            }
        }
        unset($locations_child_group[0]);
        
        // Media
        $query = new WP_Query([
            'post__not_in' => $exclude_media_ids,
            'post_type'=>'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        ]);
        $media = $query->get_posts();
        $media_ids = array_column($media, 'ID');

        $query = new WP_Query([
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        $posts = $query->get_posts();
        foreach($posts as $post) {
            // Booking
            $type = $types[array_rand($types)];
            $price = $prices[array_rand($prices)];
            update_post_meta($post->ID, 'booking_style', $type);
            update_post_meta($post->ID, 'booking_price', $price);
            
            update_post_meta($post->ID, 'social_network', json_encode($social_network));
            update_post_meta($post->ID, 'video_url', '');
            
            // Location
            $country = $locations_top_group[array_rand($locations_top_group)];
            $state_index = array_rand($locations_child_group[$country]);
            $state = $locations_child_group[$country][$state_index];

            $terms = [$country, $state];
            wp_set_post_terms($post->ID, $terms, Listar::$post_type.'_location');
            update_post_meta($post->ID, 'csc', implode(',', $terms));    

            // Tags 
            $tags_input = array_slice($tags_ids, rand(round($tags_length/2), $tags_length), rand(0, $tags_length));

            // Category
            $category_top = $category_index[0][array_rand($category_index[0])];
            if(isset($category_index[$category_top])) {
                $category_sub = $category_index[$category_top][array_rand($category_index[$category_top])];
            } else {
                $category_sub = $category_index[0][array_rand($category_index[0])];
            }

            // Feature
            $features_input = array_slice($features_ids, rand(0, $feature_length), rand(round($feature_length/2), $feature_length));


            // Random Media
            $media_index = array_rand($media);
            $thumb_id = $media[$media_index]->ID;

            // Random Gallery
            $media_slide_index = rand(0, $media_index);
            $gallery = array_slice($media_ids, $media_slide_index, rand(5, 10));

            // Set Thumb
            set_post_thumbnail($post->ID, $thumb_id);

            // Category Set
            wp_set_post_terms($post->ID, [$category_top, $category_sub], Listar::$post_type.'_category');

            // Feature set
            wp_set_post_terms($post->ID, $features_input, Listar::$post_type.'_feature');

            // Location set
            wp_set_post_terms($post->ID, [$country, $state], Listar::$post_type.'_location');

            // Tags set
            wp_set_post_terms($post->ID, $tags_input, 'post_tag');

            // Author set
            wp_update_post([
                'ID' => $post->ID,
                'post_author' => $user_index[array_rand($user_index)]
            ]);

            // Open Hours
            $open_hours = [];
            for($i=0; $i<=6; $i++) {
                $start = rand(10, 13);
                $opening_hour_start_value = implode(':', [$start, '00']);

                $end = rand(18, 23);
                $opening_hour_end_value = implode(':', [$end, '00']);

                $open_hours['opening_hour'][$i]['start'][] = $opening_hour_start_value;
                $open_hours['opening_hour'][$i]['end'][] = $opening_hour_end_value;
            }

            $row = array_merge((array) $post, $open_hours);

            // State, City > Meta data
            $row = array_merge($row, [
                'country' => $country,
                'state' => $state,
                'city' => '-1'
            ]);

            // Galleries
            $row['gallery'] = !empty($gallery) ? implode(',', $gallery) : '';

            // # Meta Insert
            Place_Model::set_metadata($post->ID, $row);
        }
    }

    /**
     * Make dummy user
     */
    public function user()
    {
        $users = [
            ['name' => 'Steve Garrett', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-2.jpg'],
            ['name' => 'Yeray Rosales', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-3.jpg'],
            ['name' => 'Alf Huncoot', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-4.jpg'],
            ['name' => 'Chioke Okonkwo', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-5.jpg'],
            ['name' => 'Ariana Grande', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-6.jpg'],
            ['name' => 'Amolika Shaikh', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-7.jpg'],
            ['name' => 'Anna Fali', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-8.jpg'],
            ['name' => 'Laquita Elliott', 'image' => 'https://listarapp.com/wp-content/uploads/2023/04/profile-4.jpg']
        ];

        foreach($users as $row) {
            $id = strtolower(str_replace(' ', '.', trim($row['name'])));
            $email = $id.'@passionui.com';
            $website = strtolower(str_replace(' ', '', trim($row['name']))).'.com';
            list($first_name, $last_name) = explode(' ', $row['name']);
            $url = $row['image'];

            $user = array(
                'user_pass'				=> md5('123456@listar'),
                'user_login' 			=> $id,
                'user_url' 				=> $website,
                'user_email' 			=> $email,
                'display_name' 			=> $row['name'],
                'first_name' 			=> $first_name,
                'last_name' 			=> $last_name,
                'role' 					=> 'author',
            );

            $user_id = username_exists($id);

            if(!$user_id) {
                $user_id = wp_insert_user( $user ) ;

                // On success.
                if ( ! is_wp_error( $user_id ) ) {
                    echo "User created : ". $user_id .' ('.$row['name'].')'.PHP_EOL;
                }
            }

            // Image download
            $user_meta_photo = get_user_meta($user_id, User_Model::$user_photo_key, true);

            if(!$user_meta_photo) {
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
                                echo $result['error'].__('URL:'.$url).'\n';
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

                                // User photo update
                                update_user_meta($user_id, User_Model::$user_photo_key, $img_id);

                                echo "Downloaded image:".$row['image'].PHP_EOL;
                            }
                        } else {
                            @unlink($temp_file);
                            echo __('Download image error. URL:'.$url).PHP_EOL;
                        }
                    } else {
                        echo "Undefine hosting".PHP_EOL;
                    }
                } else {
                    echo "Undefine mime type".PHP_EOL;
                }
            }
        }
    }

    /**
     * Import dummy comment
     */
    public function comment()
    {
        $data   = array_map('str_getcsv', file(ABSPATH.'/ocdi/comment.csv'));
        $header = array_shift($data);
        $comments = [];

        foreach($data as $row) {
            $comments[] = array_combine($header, $row);
        }

        // User
        $users = get_users(['fields' => ['display_name', 'user_email', 'ID']]);

        // Listing
        $query = new WP_Query([
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);
        $listing = $query->get_posts();
        

        foreach($listing as $post) {
            $random_start = rand(0, 99);
            $comment_list = array_slice($comments, $random_start, rand(5, 10));
            foreach($comment_list as $comment) {
                $user_index = array_rand($users);
                $user = $users[$user_index];
                $rate = rand(1, 5);
                $_POST['comment_post_ID'] = $post->ID;
                $_POST['rating'] = $rate;

                $comment_id = wp_insert_comment([
                    'comment_author' => $user->display_name,
                    'comment_author_email' => $user->user_email,
                    'comment_content' => $comment['comment'],
                    'comment_post_ID' => $post->ID,
                    'comment_author_IP' => $comment['ip'],
                    'user_id' => $user->ID
                ]);

                if(add_comment_meta( $comment_id, 'rating', $rate )) {                    
                    Comment_Model::set_rating_meta($post->ID);
                }               
            }
        }
        
        // Blog
        $query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);
        $posts = $query->get_posts();
        
        unset($_POST['comment_post_ID']);
        unset($_POST['rating']);

        foreach($posts as $post) {
            $comment_list = array_slice($comments, $random_start, rand(5, 10));
            foreach($comment_list as $comment) {
                $user_index = array_rand($users);
                $user = $users[$user_index];
                
                $comment_id = wp_insert_comment([
                    'comment_author' => $user->display_name,
                    'comment_author_email' => $user->user_email,
                    'comment_content' => $comment['comment'],
                    'comment_post_ID' => $post->ID,
                    'comment_author_IP' => $comment['ip'],
                    'user_id' => $user->ID
                ]);
            }
        }
    }

    /**
     * wp listar-dummy taxonomy_export
     */
    public function taxonomy()
    {
        $taxonony_type = Listar::$post_type.'_location';
        $taxonony = get_terms($taxonony_type, [
            'hide_empty' => 0
        ]);

        $dir = wp_upload_dir();
        $fp = fopen($dir['basedir'].'/taxonony.csv', 'w');
        
        foreach($taxonony as $term) {
            $icon = get_term_meta($term->term_id, 'icon', true);
            $color = get_term_meta($term->term_id, 'color', true);
            $featured_image = get_term_meta($term->term_id, 'featured_image', true);
            $image_url = '';
            if((int)$featured_image > 0) {
                $image_url = wp_get_attachment_url($featured_image);
            }

            $parent = NULL;
            $parent = get_term_by('id', $term->parent, $taxonony_type);

            $csv_data = [
                'title' => $term->name,
                'description' => $term->description,
                'color' => $color,
                'icon' => $icon,
                'image' => $image_url,
                'parent' => !empty($parent) ? $parent->name : '',
            ];

            fputcsv($fp, $csv_data);
        }

        fclose($fp);
    }

    /**
     * Image
     */
    public static function image() {
        // Media
        $query = new WP_Query([
            'post_type'=>'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
        ]);
        $media = $query->get_posts();
        $media_ids = array_column($media, 'ID');

        // Post
        $query = new WP_Query([
            'post_type'=>'listar',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);
        $posts = $query->get_posts();

        foreach($posts as $post) {
            // Random Media
            $media_index = array_rand($media);
            $thumb_id = $media[$media_index]->ID;

            // Random Gallery
            $media_slide_index = rand(0, $media_index);
            $gallery = array_slice($media_ids, $media_slide_index, rand(5, 10));

            // # Set Thumb
            set_post_thumbnail($post->ID, $thumb_id);

            // # Set Gallery
            $gallery = !empty($gallery) ? implode(',', $gallery) : '';
            update_post_meta($post->ID, 'gallery', $gallery);
        }
    }
}