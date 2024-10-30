<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Libraries\Api_Interface_Controller;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;

class Api_Debug_Controller extends WP_REST_Controller
    implements Api_Interface_Controller
{

    public function __construct()
    {
        $this->namespace = 'listar/v1';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/debug/email', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'email'],
                'permission_callback' => '__return_true'
            ]
        ]);

        register_rest_route($this->namespace, '/debug/generate', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'generate'],
                'permission_callback' => '__return_true'
            ]
        ]);
    }

    /**
     * Debug sending email
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.11
     */
    public function email($request)
    {
        $message = 'Listar > Test';
        $title = 'Listar > Test';
        $user_email = 'windy2k7@gmail.com';


        if ( $message && !wp_mail($user_email, $title, $message)) {
            return rest_ensure_response([
                'code' => 'auth_reset_password',
                'message' => __('The e-mail could not be sent.') . __('Possible reason: your host may have disabled the mail() function...'),
                'data' => [
                    'status' => 403
                ]
            ]);
        }

        return rest_ensure_response([
            'success' => TRUE,
            'data' => [
            ]
        ]);
    }

    public function generate()
    {
        $types = ['standard', 'slot', 'hourly', 'daily'];
        $prices = [39, 49, 59, 69];
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

        // Location
        $locations = get_terms( Listar::$post_type.'_location', [
            'hide_empty' => 0
        ]);

        $locations_top_group = [];
        $locations_child_group = [];
        foreach($locations as $item) {
            $locations_child_group[$item->parent][] = $item->term_id;
            if($item->parent == 0) {
                $locations_top_group[] = $item->term_id;
            }
        }
        unset($locations_child_group[0]);

        $query = new WP_Query([
            'post_type'         => Listar::$post_type,
            'post_status'       => 'publish',
            'posts_per_page'    => -1
        ]);
        $posts = $query->get_posts();

        foreach($posts as $post) {
            //$type = $types[array_rand($types)];
            //$price = $prices[array_rand($prices)];
            //update_post_meta($post->ID, 'booking_style', $type);
            //update_post_meta($post->ID, 'booking_price', $price);
            //update_post_meta($post->ID, 'social_network', json_encode($social_network));
            update_post_meta($post->ID, 'video_url', 'https://listarapp.com/wp-content/uploads/2020/08/video-demo-01.mp4');
            // Random Location
            //$country = $locations_top_group[array_rand($locations_top_group)];
            //$state_index = array_rand($locations_child_group[$country]);
            //$state = $locations_child_group[$country][$state_index];

            // #Location set
            //$terms = [$country, $state];
            //wp_set_post_terms($post->ID, $terms, Listar::$post_type.'_location');
            //update_post_meta($post->ID, 'csc', implode(',', $terms));
        }
    }
}
