<?php
/**
 * Make json data for mobile widgets
 * - Circle
 * - Square
 * - Icon
 */
namespace ListarWP\Plugin\Widgets\Api;
use ListarWP\Plugin\Models\Banner_Model;
use ListarWP\Plugin\Models\Setting_Model;
use WP_Widget;
use ListarWP\Plugin\Listar;
use \Exception;

class Banner extends WP_Widget
{   
    /**
     * Sidebar ID
     * @var string
     */
    protected $sidebar_id = '';

    /**
     * Post type of term
     * @var string
     */
    static $post_type = '';

    /**
     * Hide Title
     * @var bool
     */
    protected $hide_title = false;

    /**
     * Hide Desc
     * @var bool
     */
    protected $hide_desc = false;

    /**
     * Show description
     * @var bool
     */
    protected $desciption = false;

    /**
     * Style display widget
     * @var string
     */
    static $type= 'banner';

    /**
     * Direction format
     * - horizontal : ==
     * - vertical : ||
     * @var string
     */
    protected $direction = 'vertical';

    /**
     * Include only these term_ids
     * @var array
     */
    protected $term_ids = [];

    /**
     * Before title
     * @var string
     */
    protected $before_title = '';

    /**
     * After
     * @var string
     */
    protected $after_title = '';

    /**
     * Before widget
     * @var string
     */
    protected $before_widget = '';

    /**
     * After widget
     * @var string
     */
    protected $after_widget = '';

    /**
     * Options
     * @param array $args
     */
    public function __construct($args = [])
    {
        if(!empty($args)) {
            if(isset($args['id'])) {
                $this->id_base = $args['id'];
            }

            if(isset($args['id'])) {
                $this->name = $args['name'];
            }
        }

        if(!$this->id_base) {
            $this->id_base = 'listar_api_banner';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Api Banner', 'listar');
        }

        self::$post_type = Listar::$post_type . '_banner';

        $this->before_widget = '';
        $this->after_widget = '';

        parent::__construct($this->id_base, $this->name);

        $this->initialize($args);        
    }

    /**
     * Install options
     * @param array $args
     */
    public function initialize($args = [])
    {
        if(is_array($args) && !empty($args)) {
            foreach ($args as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
        }
    }

    /**
     * Load script
     * @version 1.0.25
     */
    public function load_scripts()
    {
        $listar = Listar::get_instance();
        $assets_url = $listar->plugin_url().'/assets';
        // Remove cache by admin suffix time
        $version = $listar::$version;
        if(defined('WP_DEBUG') && WP_DEBUG) {
            $version .= '.'.time();
        }
        // Jquery UI Date picker & slider
        //wp_enqueue_script('jquery-ui-core', false, ['jquery']);
        // Load standalone script
        wp_enqueue_script('listar-admin-banner-js', $assets_url . '/js/admin-banner.js', []);
    }

    /**
     * Get data for mobile
     * @param array $instance
     * @return array
     */
    public static function json($instance = [])
    {
        $type = isset($instance['type']) ? $instance['type'] : self::$type;
        $data = [];

        switch ($type) {
            case 'banner':
                try {
                    $banner_id = isset($instance['banner_id']) ? $instance['banner_id'] : 0;
                    $post = get_post((int) $banner_id);
                    if (empty($post) || empty($post->ID) || $post->post_type !== Listar::$post_type . '_banner') {
                        throw new Exception(__('Invalid banner data. #ID='.$banner_id, 'listar'));
                    }
                    Banner_Model::assign_meta_data($post);
                    $image_id = get_post_thumbnail_id($post->ID);
                    if ((int)$image_id > 0) {
                        $post->image = [
                            'id' => get_post_thumbnail_id($post->ID),
                            'full' => ['url' => get_the_post_thumbnail_url($post->ID)],
                            'medium' => ['url' => get_the_post_thumbnail_url($post->ID, 'medium')],
                            'thumb' => ['url' => get_the_post_thumbnail_url($post->ID, 'thumb')],
                        ];
                    } else {
                        $post->image = Setting_Model::default_image();
                    }
                    $data = [
                        'data' => [
                            'image' => $post->image,
                            'url' => $post->ads_url
                        ]
                    ];
                } catch (Exception $e) {
                    error_log('widget.banner: '.$e->getMessage());
                }
                break;
            case 'slider':
                $rows = [];
                try {
                    $banner_id = isset($instance['banner_id']) ? $instance['banner_id'] : '0';
                    $posts = get_posts([
                        'post__in' => str_replace(' ', '', explode(",", $banner_id)),
                        'post_type' => Listar::$post_type . '_banner',
                        'orderby'   => 'post__in',
                    ]);
                    if (empty($posts)) {
                        throw new Exception(__('Empty banner data.', 'listar'));
                    }
                    foreach($posts as $post) {
                        $image_id = get_post_thumbnail_id($post->ID);
                        if ((int)$image_id > 0) {
                            $post->image = [
                                'id' => get_post_thumbnail_id($post->ID),
                                'full' => ['url' => get_the_post_thumbnail_url($post->ID)],
                                'medium' => ['url' => get_the_post_thumbnail_url($post->ID, 'medium')],
                                'thumb' => ['url' => get_the_post_thumbnail_url($post->ID, 'thumb')],
                            ];
                        } else {
                            $post->image = Setting_Model::default_image();
                        }

                        $rows[] = [
                            'image' => $post->image,
                            'url' => $post->ads_url
                        ];
                    }
                } catch (Exception $e) {
                    error_log('widget.slider: '.$e->getMessage());
                }
                $data['data'] = $rows;
                break;
            case 'admob':
                $data = [
                    'data' =>[
                        'android' => isset($instance['admob_android']) ? $instance['admob_android'] : '',
                        'ios' => isset($instance['admob_ios']) ? $instance['admob_ios'] : '',
                        'size' => [
                            'width' => 320,
                            'height' => 50,
                        ]
                    ]
                ];
                break;
        }
        
        return array_merge([
            'title' => isset($instance['title']) ? $instance['title'] : '',
            'description' => isset($instance['desc']) ? $instance['desc'] : '',
            'hide_title' => isset($instance['hide_title']) && (int) $instance['hide_title'] === 1,
            'hide_desc' => isset($instance['hide_desc']) && (int) $instance['hide_desc'] === 1,
            'type' => $type,
        ], $data);
    }

    /**
     * Widget render html/content
     * @param array $args | Optional
     * @param array $instance | setting from UI
     */
    public function widget($args = [], $instance = [])
    {   
        if(isset($args['id'])) {
            $this->sidebar_id = $args['id'];
        }
        $this->initialize($args);
        $this->initialize($instance);

        echo wp_kses_post($this->before_widget);

        if (!empty($instance['title']) && !$this->hide_title) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
            if (isset($instance['desc']) && !empty($instance['desc']) && !$this->hide_desc) {
                echo '<p class="text-muted">' . $instance['desc'] . '</p>';
            }
        }

        echo '<div class="row">';

        echo '</div>';

        echo wp_kses_post($this->after_widget);
    }

    /**
     * Widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {       
        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
        
        $type_options   = [
            'banner' => __('Banner', 'listar'),
            'slider' => __('Slider Banner', 'listar'),
            'admob' => __('Google Admob', 'listar'),
        ];
        $type = isset($instance['type']) ? esc_attr($instance['type']) : self::$type;
        ?>
        <p>            
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
            <input type="text"
                class="widefat"
                id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>"  
            />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><b><?php _e('Description', 'listar'); ?></b></label>
            <textarea class="widefat"
                      id="<?php echo esc_attr($this->get_field_id('desc')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('desc')); ?>"
                      rows="3"> <?php echo isset($instance['desc']) ? esc_attr($instance['desc']) : ''; ?></textarea>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hide_title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_title')); ?>"
                   value="1" <?php echo isset($instance['hide_title']) && absint($instance['hide_title']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hide_title')); ?>"><?php _e('Hide Title', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hide_desc')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_desc')); ?>"
                   value="1" <?php echo isset($instance['hide_desc']) && absint($instance['hide_desc']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hide_desc')); ?>"><?php _e('Hide Description', 'listar'); ?></label>
        </p>
        <p><!-- Type -->
            <label><b><?php _e('Banner Type', 'listar'); ?></b></label><br/>
            <?php foreach($type_options as $value => $label) { ?>
                <label style="padding-right: 20px">
                    <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('type')); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       id="<?php echo esc_attr($this->get_field_id('type')); ?>"
                        <?php echo esc_attr($type === $value) ? 'checked="checked"' : ''; ?>
                    >
                    <?php echo esc_attr($label); ?>
                </label>
            <?php } ?>
        </p>      
        <!-- Banner ID -->  
        <p class="<?php echo $type !== 'admob' ? '' : 'listar-hidden';?>" id="<?php echo esc_attr($this->get_field_id('banner_elm')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('banner_id')); ?>"><b><?php _e('Banner ID', 'listar'); ?></b></label>
            <input type="text"
               class="widefat"
               id="<?php echo esc_attr($this->get_field_id('banner_id')); ?>"
               name="<?php echo esc_attr($this->get_field_name('banner_id')); ?>"
               value="<?php echo isset($instance['banner_id']) ? esc_attr($instance['banner_id']) : ''; ?>" />
        </p>
        <!-- Admob ID -->
        <p class="<?php echo $type === 'admob' ? '' : 'listar-hidden';?>" id="<?php echo esc_attr($this->get_field_id('admob_code')); ?>">
            <label><?php _e('Admob Android Code', 'listar'); ?></label><br/>
            <input type="text"
                class="widefat"
                id="<?php echo esc_attr($this->get_field_id('admob_android')); ?>"
                name="<?php echo esc_attr($this->get_field_name('admob_android')); ?>"
                placeholder="ca-app-pub-xxxx"
                value="<?php echo isset($instance['admob_android']) ? esc_attr($instance['admob_android']) : ''; ?>" />
            <label><?php _e('Admob IOS Code', 'listar'); ?></label><br/>    
            <input type="text"
                class="widefat"
                id="<?php echo esc_attr($this->get_field_id('admob_ios')); ?>"
                name="<?php echo esc_attr($this->get_field_name('admob_ios')); ?>"
                placeholder="ca-app-pub-yyyy"
                value="<?php echo isset($instance['admob_ios']) ? esc_attr($instance['admob_ios']) : ''; ?>" />
        </p>
        <?php
    }

    /**
     * Widget save data handler
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [
            'title' => (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '',
            'desc' => (!empty($new_instance['desc'])) ? trim(sanitize_textarea_field($new_instance['desc'])) : '',
            'hide_title' => (!empty($new_instance['hide_title'])) ? $new_instance['hide_title'] : 0,
            'hide_desc' => (!empty($new_instance['hide_desc'])) ? $new_instance['hide_desc'] : 0,
            'type' => (!empty($new_instance['type'])) ? $new_instance['type'] : self::$type,
            'banner_id' => (!empty($new_instance['banner_id'])) ? $new_instance['banner_id'] : '',
            'admob_ios' => (!empty($new_instance['admob_ios'])) ? $new_instance['admob_ios'] : '',
            'admob_android' => (!empty($new_instance['admob_android'])) ? $new_instance['admob_android'] : '',
        ];

        return $instance;
    }
}
?>
