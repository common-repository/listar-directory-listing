<?php
use ListarWP\Plugin\Models\Setting_Model;

if(!function_exists("debug")) {
    /**
     * Debug function
     *
     * @param array $data
     * @param boolean $exit
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function debug($data = [], $exit = FALSE) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if($exit) {
            exit();
        }
    }
}

if (!function_exists('getallheaders')) {
    /**
     * Parse header information
     * @return array
     * @since 1.0.6
     */
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if(!function_exists("listar_get_range_time")) {
    /**
     * Get range tome
     *
     * @param integer $lower
     * @param integer $upper
     * @param integer $step
     * @param string $format
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @throws Exception
     * @since 1.0.0
     */
    function listar_get_range_time($lower = 0, $upper = 23, $step = 1, $format = NULL) {
        $times = [];
        if ($format === NULL) {
            $format = get_option('time_format'); // 9:30pm
        }

        $start = new \DateTime('00:00');
        $range = $upper * $step; // 24 hours * 30 min in an hour

        if($step == 1) {
            $interval = '1H';
        } else if($step > 1) {
            $interval = 60/$step.'M';
        }

        for ($i = $lower; $i < $range; $i++) {
            $start->add(new \DateInterval('PT'.$interval));
            $key = $start->format('H:i');
            $times[$key] = $start->format($format);
        }

        return $times;
    }
}

if(!function_exists("listar_get_single_value")) {
    /**
     * convert multiple to single value from array given
     *
     * @param array $val
     * @return mixed
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function listar_get_single_value($val) {
        return isset($val[0]) ? $val[0] : NULL;
    }
}

if(!function_exists("listar_convert_single_value")) {
    /**
     * Convert array with item multiple to single value
     *
     * @param array $data
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function listar_convert_single_value($data = []) {
        return array_map('listar_get_single_value', $data);
    }
}

if(!function_exists("is_edit_page")) {
    /**
     * Convert array with item multiple to single value
     *
     * @param null $new_edit
     * @return bool
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function is_edit_page($new_edit = null){
        global $pagenow;

        //make sure we are on the backend
        if (!is_admin()) return false;

        if($new_edit == "edit")
            return in_array( $pagenow, ['post.php'] );
        elseif($new_edit == "new") //check for new post page
            return in_array( $pagenow, ['post-new.php'] );
        else //check for either new or edit
            return in_array( $pagenow, ['post.php', 'post-new.php']);
    }
}

if(!function_exists('get_nav_menu_items_by_location')) {
    /**
     * Get menu data from location
     * - WP Admin > Appearance > Menu > Mobile Dashboard Location
     * @param string $location Get from function register_nav_menu($location)
     * @param array $args
     * @return array|false
     */
    function get_nav_menu_items_by_location($location, $args = [])
    {
        // Get all locations (all data from Appearance > Menu > Manage Location)
        $locations = get_nav_menu_locations();

        // Get object id by location (menu ID)
        if(isset($locations[$location])) {
            $object = wp_get_nav_menu_object($locations[$location]);
        } else {
            return [];
        }

        // Get menu items by menu name (extract menu data)
        if($object) {
            $menu_items = wp_get_nav_menu_items($object->name, $args);
        } else {
            $menu_items = [];
        }

        // Return menu post objects
        return $menu_items;
    }
}

if(!function_exists('listar_get_open_hours_label')) {
    /**
     * Get list data day of week for open hours list data
     * @return array
     * @version 1.0.1
     */
    function listar_get_open_hours_label() {
        return [
            [
                'label' => __('Mon'),
                'key' => 'mon',
                'day_of_week' => 1,
            ],
            [
                'label' => __('Tue'),
                'key' => 'tue',
                'day_of_week' => 2,
            ],
            [
                'label' => __('Wed'),
                'key' => 'wed',
                'day_of_week' => 3,
            ],
            [
                'label' => __('Thu'),
                'key' => 'thu',
                'day_of_week' => 4,
            ],
            [
                'label' => __('Fri'),
                'key' => 'fri',
                'day_of_week' => 5,
            ],
            [
                'label' => __('Sat'),
                'key' => 'sat',
                'day_of_week' => 6,
            ],
            [
                'label' => __('Sun'),
                'key' => 'sun',
                'day_of_week' => 0,
            ]
        ];
    }
}

if(!function_exists('listar_get_sort_option')) {
    /**
     * Listar get sort option
     * @return array
     *
     */
    function listar_get_sort_option() {
        return [
            [
                'title' => __('Lastest Post', 'listar'),
                'field' => 'post_date',
                'lang_key' => 'post_date_desc',
                'value' => 'DESC'
            ],
            [
                'title' => __('Oldest Post', 'listar'),
                'field' => 'post_date',
                'lang_key' => 'post_date_asc',
                'value' => 'ASC'
            ],
            [
                'title' => __('Most Views', 'listar'),
                'field' => 'comment_count',
                'lang_key' => 'comment_count_desc',
                'value' => 'DESC'
            ]
        ];
    }
}

if(!function_exists('listar_send_device_notification_broadcast')) {
    /**
     * Send notification via FCM
     * - Refer more https://firebase.google.com/docs/cloud-messaging/send-message
     * @param array|string $device_token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array|bool|WP_Error
     * @version 1.0.3
     * @throws Exception
     */
    function listar_send_device_notification_broadcast($device_token, $title = '', $body = '', $data = [])
    {   
        if (!empty($device_token) && $device_token != 'NULL') {
            $device_token = json_decode(json_encode($device_token));        

            $fcm_key = esc_attr(get_option('listar_fcm_key'));
            if(!$fcm_key) {
                throw new Exception(_('Please define Firebase Settings at Settings > Notification > General', 'listar'));
            }

            $notification = [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => '1',
            ];

            $args = [
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.1',
                'method' => 'POST',
                'body' => json_encode([
                    'registration_ids' => $device_token,
                    'notification' => $notification,
                    'data' => $data,
                    'priority' => 'high'
                ]),
                'sslverify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=' . $fcm_key,
                ],
                'cookies' => []
            ];

            return wp_remote_post('https://fcm.googleapis.com/fcm/send', $args);
        }

        return FALSE;
    }
}

if(!function_exists('listar_mobile_push_message')) {
    /**
     * Send notification via FCM
     * - Refer more https://firebase.google.com/docs/cloud-messaging/send-message
     * @param array|string $device_token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array|bool|WP_Error
     * @version 1.0.34
     * @throws Exception
     */
    function listar_mobile_push_message($device_token, $title = '', $body = '', $data = [])
    {   
        $project_id = Setting_Model::get_option('fcm_project_id');
        
        if(!$project_id) {
            throw new Exception('Missing Settings > Notification > General > Firebase Project ID.');
        }

        if(!defined('LISTAR_FIREBASE_HTTP_V1_KEY')) {
            throw new Exception('Undefine setting file Firebase private key.');
        }

        if(!file_exists(LISTAR_FIREBASE_HTTP_V1_KEY)) {
            throw new Exception('Setting Firebase private key is not exists.');
        }

        $url = 'https://fcm.googleapis.com/v1/projects/'.$project_id.'/messages:send';
        
        if(!class_exists('Google\Client')) {
            throw new Exception('Missing class Google\Client. Run the command for install > composer require google/apiclient.');
        }

        $client = new Google\Client();
        $client->setAuthConfig(LISTAR_FIREBASE_HTTP_V1_KEY);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $token = $client->fetchAccessTokenWithAssertion();
        
        if(!$token || !isset($token['access_token'])) {
            throw new Exception('Undefine access token Firebase push message.');
        }        

        $message = [
            'token' => $device_token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],                  
        ];
        
        if(is_array($data) && !empty($data)) {
            // Make sure push data is
            foreach($data as $key => &$value) {
                if(is_array($value)) {
                    unset($data[$key]);
               } else if(!is_string($value)) {
                    $value = (string) $value;
               } 
            }
            $message['data'] = $data;
        }

        $headers = [
            'Authorization: Bearer ' . $token['access_token'],
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('[Firebase][Curl_Error]: ' . curl_error($ch));
        }
        curl_close($ch);
        $result = json_decode($response, true);
        return $result;
    }
}

if(!function_exists('listar_get_opening_hour')) {
    /**
     * Parse opening hours data
     * @param array $meta_data
     * @return array
     */
    function listar_get_opening_hour($meta_data = []) {
        $opening_hour_pattern = "/^(opening_hour)\_([0-9]{1})\_([a-z]+)/";
        $opening_hour_data = [];
        foreach($meta_data as $key => $value) {
            preg_match($opening_hour_pattern, $key, $matches);
            if( $matches) {
                $opening_hour_data[$matches[2]][$matches[3]] = $value;
                unset($meta_data[$key]);
            }
        }

        $day_of_weeks = listar_get_open_hours_label();
        foreach($day_of_weeks as &$row) {
            if(isset($opening_hour_data[$row['day_of_week']])) {
                $start_data = isset($opening_hour_data[$row['day_of_week']]['start']) ?
                    $opening_hour_data[$row['day_of_week']]['start'] : [];
                $end_data = isset($opening_hour_data[$row['day_of_week']]['end']) ?
                    $opening_hour_data[$row['day_of_week']]['end'] : [];

                if(!empty($start_data)) {
                    foreach($start_data as $key => $time) {
                        $row['schedule'][] = [
                            'start' => $time,
                            'end' => $end_data[$key],
                        ];
                    }
                }
            } else {
                $row['schedule'] = [];
            }
        }

        return $day_of_weeks;
    }
}

if(!function_exists('listar_get_opening_hour_status')) {
    /**
     * Get open status base on open hour settings
     * @param array $metadata
     * @param string $status_opening
     * @param string $status_closed
     * @return string
     */
    function listar_get_opening_hour_status($metadata = [], $status_opening = 'Opening', $status_closed = 'Closed') {
        $today_week = (int)date('w');
        $time_now = current_datetime()->format('H:i'); // Settings > General > Timezone
        $opening_hour_pattern = "/^(opening_hour)\_([0-9]{1})\_([a-z]+)/";
        $opening_hour_data = [];            
        
        foreach($metadata as $key => $value) {
            preg_match($opening_hour_pattern, $key, $matches);
            if( $matches && $matches[2] == $today_week) {
                $opening_hour_data[$matches[3]] = $value;
                unset($metadata[$key]);
               
            }
        }   

        if(!empty($opening_hour_data)) {
            if(is_array($opening_hour_data['start'])) {
                foreach($opening_hour_data['start'] as $index => $start_time) {
                    if($time_now >= $start_time && $time_now <= $opening_hour_data['end'][$index]) {
                        return $status_opening;
                    }
                }
                return $status_closed;
            } else if(is_string($opening_hour_data['start'])) {
                if($time_now >= $opening_hour_data['start'] && $time_now <= $opening_hour_data['end']) {
                    return $status_opening;
                } else {
                    return $status_closed;
                }
            }
        }

        if(is_array($metadata['status'])) {
            return isset($metadata['status'][0]) ? $metadata['status'][0] : '';
        } else if(is_string($metadata['status'])) {
            return $metadata['status'];
        }        
    }
}

if (!function_exists('listar_theme_option')) {
    /**
     * Listar theme option
     * @param string $option
     * @return mixed
     */
    function listar_theme_option($option = '')
    {
        return Setting_Model::get_option($option);
    }
}

if(!function_exists('listar_days_between')) {
    /**
     * Counting days between
     * @param string $date1
     * @param string $date2
     * @param bool $total
     * @return int
     */
    function listar_days_between($date1 = '', $date2 = '', $total = FALSE)
    {
        $str_date1 = strtotime($date1);
        $str_date2 = strtotime($date2);
        $time = $str_date1 - $str_date2;
        $time = $time < 0 ? $time * -1 : '';
        $days_between = $time > 0 ? floor($time / 60 / 60 / 24) : 0;
        return $total ? (int)$days_between + 1 : (int)$days_between;
    }
}

if(!function_exists('listar_hours_between')) {
    function listar_hours_between($start_time = '', $end_time = '', $round = TRUE)
    {
        $time_segment = explode(':', $start_time);
        $start_min =  (int)$time_segment[0] * 60 + (int)$time_segment[1];

        $time_segment = explode(':', $end_time);
        $end_min =  (int)$time_segment[0] * 60 + (int)$time_segment[1];

        $diff_min = $end_min - $start_min;

        if($diff_min > 0) {
            if($round) {
                return floor($diff_min/60);
            } else {
                return number_format((float)$diff_min/60, 2, '.', '');
            }
        }

        return 0;
    }
}

if(!function_exists('listar_schedule_slot')) {
    function listar_schedule_slot($start = '09:00', $end  = '18:00', $duration = 60, $format = NULL)
    {
        if ($format === NULL) {
            $format = get_option('time_format'); // 9:30pm
        }
        $time = [];
        if($start == '00:00' || $start == '24:00') {
            $start = '23:59';
        }
        if($end == '00:00' || $end == '24:00') {
            $end = '23:59';
        }

        $start = new DateTime($start);
        $end = new DateTime($end);
        $start_time = $start->format('H:i');
        $end_time = $end->format('H:i');
        $i=0;
        
        while(strtotime($start_time) <= strtotime($end_time)){
            $start = $start_time;
            $end = date('H:i',strtotime('+'.$duration.' minutes',strtotime($start_time)));
            $start_time = date('H:i',strtotime('+'.$duration.' minutes',strtotime($start_time)));

            if($end == '00:00' && $start_time == '00:00') {
                break;
            }

            if(strtotime($start_time) <= strtotime($end_time)){
                $time[$i]['format'] = sprintf('%s - %s', date($format, strtotime($start)), date($format, strtotime($end)));
                $time[$i]['start'] = $start;
                $time[$i]['end'] = $end;
            }
            $i++;            
        }
        return $time;
    }
}

if(!function_exists('listar_booking_status')) {
    /**
     * Get booking status
     *
     * @return array
     */
    function listar_booking_status()
    {
        return [
            'completed' => [
                'title' => __('Completed', 'listar'),
                'color' => '#58d68d'
            ],
            'processing' => [
                'title' => __('Processing', 'listar'),
                'color' => '#e5634d'
            ],
            'canceled' => [
                'title' => __('Canceled', 'listar'),
                'color' => '#3c5a99'
            ],
            'failed' => [
                'title' => __('Failed', 'listar'),
                'color' => '#ff6363'
            ],
            'refunded' => [
                'title' => __('Refunded', 'listar'),
                'color' => '#a569bd'
            ],
            'pending' => [
                'title' => __('Pending', 'listar'),
                'color' => '#e5634d'
            ],
            'publish' => [
                'title' => __('Publish', 'listar'),
                'color' => '#58d68d'
            ]
        ];
    }
}

if(!function_exists('listar_claim_status')) {
    /**
     * Get claim status
     *
     * @return array
     */
    function listar_claim_status()
    {
        return [
            'pending' => [
                'title' => __('Pending', 'listar'),
                'color' => '#e5634d'
            ],    
            'publish' => [
                'title' => __('Approved', 'listar'),
                'color' => '#58d68d'
            ],            
            'rejected' => [
                'title' => __('Rejected', 'listar'),
                'color' => '#ff6363'
            ], 
            'completed' => [
                'title' => __('Completed', 'listar'),
                'color' => '#58d68d'
            ],  
            'canceled' => [
                'title' => __('Canceled', 'listar'),
                'color' => '#3c5a99'
            ],
            'failed' => [
                'title' => __('Failed', 'listar'),
                'color' => '#ff6363'
            ],
            'refunded' => [
                'title' => __('Refunded', 'listar'),
                'color' => '#a569bd'
            ]                  
        ];
    }
}

if(!function_exists('listar_claim_method_charges')) {
    /**
     * Get claim method charges
     *
     * @return array
     */
    function listar_claim_method_charges()
    {
        return [
            'free' => [
                'title' => __('Free', 'listar'),
                'color' => '#e5634d'
            ],    
            'pay' => [
                'title' => __('Pay', 'listar'),
                'color' => '#58d68d'
            ]           
        ];
    }
}

if(!function_exists('listar_booking_support_filed')) {
    /**
     * Check support fields
     * @param string $data
     * @param string $field
     * @param string $booking_type
     * @return bool
     */
    function listar_booking_support_filed($data = '', $field = '', $booking_type = '')
    {
        if($data) {
            $support = explode('|', $data);
            if(in_array($booking_type, $support)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}

if(!function_exists('listar_is_admin_user')) {
    /**
     * Check is admin
     * @return bool
     */
    function listar_is_admin_user()
    {
        return current_user_can('manage_options');
    }
}

if(!function_exists('listar_get_image')) {
    /**
     * Get attachment id
     * - Support set default image if image was empty
     * @since 1.0.15
     * @param int $attachment_id
     * @return array
     */
    function listar_get_image($attachment_id = 0)
    {
        $attachment_id = absint($attachment_id);
        // @size thumbnail|medium|large
        $size = 'thumbnail';
        $image_thumbnail= wp_get_attachment_image_src( $attachment_id, $size);
        if(empty($image_thumbnail)) {
            $default_image = Setting_Model::default_image();
        } else {
            $size = 'large';
            $image_large = wp_get_attachment_image_src( $attachment_id, $size);

            $size = 'medium';

            $image_medium = wp_get_attachment_image_src( $attachment_id, $size);
            $default_image = [
                'id' => $attachment_id,
                'thumb' => ['url' => $image_thumbnail[0]],
                'medium' => ['url' => $image_medium[0]],
                'full' => ['url' => $image_large[0]]
            ];
        }

        return $default_image;
    }
}

if(!function_exists('listar_pattern_replace')) {
    /**
     * @param array $post
     * @param string $$subject
     * @return string
     * @since 1.0.21
     */
    function listar_pattern_replace($post, $subject = '')
    {   

        $replace = (array) $post;
        $search = array_map(function ($v) {return "{{$v}}";}, array_keys($replace));
        $result = @str_replace($search, $replace, $subject);
        return $result;
    }
}
if(!function_exists('listar_gps_distance')) {
    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    function listar_gps_distance($lat1, $lon1, $lat2, $lon2)
    {
        $unit = Setting_Model::get_option('measure_distance_unit');

        if($lat1 && $lon1 && $lat2 && $lon2) {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist); 
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if (strtolower($unit) == "k") {
                return round($miles * 1.609344, 2).' '.__('km', 'listar');
            } else {
                return round($miles, 2).' '.__('miles', 'listar');
            }
        } else {
            return  '';
        }
    }
}

if(!function_exists('listar_gps_random_point')) {
    /**
     * Create random point
     * @param array $centre [lat, long]
     * @param int $radius mile 
     */
    function listar_gps_random_point($centre = [], $radius = 0) {
        $radius_earth = 3959; //miles
    
        //Pick random distance within $distance;
        $distance = lcg_value()*$radius;
    
        //Convert degrees to radians.
        $centre_rads = array_map( 'deg2rad', $centre );
    
        //First suppose our point is the north pole.
        //Find a random point $distance miles away
        $lat_rads = (pi()/2) -  $distance/$radius_earth;
        $lng_rads = lcg_value()*2*pi();
    
    
        //($lat_rads,$lng_rads) is a point on the circle which is
        //$distance miles from the north pole. Convert to Cartesian
        $x1 = cos( $lat_rads ) * sin( $lng_rads );
        $y1 = cos( $lat_rads ) * cos( $lng_rads );
        $z1 = sin( $lat_rads );
    
    
        //Rotate that sphere so that the north pole is now at $centre.
    
        //Rotate in x axis by $rot = (pi()/2) - $centre_rads[0];
        $rot = (pi()/2) - $centre_rads[0];
        $x2 = $x1;
        $y2 = $y1 * cos( $rot ) + $z1 * sin( $rot );
        $z2 = -$y1 * sin( $rot ) + $z1 * cos( $rot );
    
        //Rotate in z axis by $rot = $centre_rads[1]
        $rot = $centre_rads[1];
        $x3 = $x2 * cos( $rot ) + $y2 * sin( $rot );
        $y3 = -$x2 * sin( $rot ) + $y2 * cos( $rot );
        $z3 = $z2;
    
    
        //Finally convert this point to polar co-ords
        $lng_rads = atan2( $x3, $y3 );
        $lat_rads = asin( $z3 );
    
        return array_map( 'rad2deg', array( $lat_rads, $lng_rads ) );
    }
}

if(!function_exists('listar_array_to_csv_download')) {
    /**
     * Export data to csv
     */
    function listar_array_to_csv_download($array, $filename = "export.csv") {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        ob_clean();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        exit();
    }   
}

if(!function_exists('listar_csv_to_array')) {
    /**
     * Reading csv upload file
     *
     * @param string $file_name
     * @param string $delimiter
     * @param string $active_tab
     * @version 1.0.26
     * @return array|bool
     */
    function listar_csv_to_array($file_name='', $delimiter=',') {
        if(!file_exists($file_name) || !is_readable($file_name))
            throw new Exception(__('Can no read file', 'listar'));

        $header = NULL;
        $data = array();
        if (($handle = fopen($file_name, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if(!$header) {
                    $header = $row;
                } else {
                    $row_combine = array_combine($header, $row);
                    $data[] = $row_combine;
                }
            }
            fclose($handle);
        }
        return $data;
    }
}

if(!function_exists('listar_get_listing_sort_option')) {
    /**
     * Get list sort options
     * @return array[]
     */
    function listar_get_listing_sort_option()
    {
        return [
            ['title' => __('Created Date Desc', 'listar-wp'), 'option' => ['orderby' => 'date', 'order' => 'desc']],
            ['title' => __('Created Date Asc', 'listar-wp'), 'option' => ['orderby' => 'date', 'order' => 'asc']],
            ['title' => __('A to Z', 'listar-wp'), 'option' => ['orderby' => 'title', 'order' => 'asc']],
            ['title' => __('Z to A', 'listar-wp'), 'option' => ['orderby' => 'title', 'order' => 'desc']],
            ['title' => __('Top Comment', 'listar-wp'), 'option' => ['orderby' => 'comment_count', 'order' => 'desc']],
            ['title' => __('Top Rating', 'listar-wp'), 'option' => ['orderby' => 'meta_value_num', 'order' => 'desc', 'meta_key' => 'rating_avg']]
        ];
    }
}

if(!function_exists('listar_array_insert_after')) {
    /**
     * Insert key after
     *
     * @param string $key
     * @param array $array
     * @param string $new_key
     * @param string|bool|int $new_value
     * @return boolean|array
     */
    function listar_array_insert_after($key, array &$array, $new_key, $new_value) {
        if (array_key_exists ($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                $new[$k] = $value;
                if ($k === $key) {
                    $new[$new_key] = $new_value;
                }
            }
            return $new;
        }
        return FALSE;
    }
}
