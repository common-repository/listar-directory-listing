<?php
namespace ListarWP\Plugin\Widgets\Common;
use WP_Widget;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Walkers\Sidebar_Menu;

class Menu extends WP_Widget
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
    protected $post_type = '';

    /**
     * Count
     * @var bool
     */
    protected $count = true;

    /**
     * Use thumb
     * @var bool
     */
    protected $thumb = false;

    /**
     * Use icon
     * @var bool
     */
    protected $icon = false;

    /**
     * Show description
     * @var bool
     */
    protected $desciption = false;

    /**
     * Show dropdown menu leve
     * @var bool
     */
    protected $dropdown = false;

    /**
     * Show only top level
     * @var bool
     */
    protected $top_level = false;

    /**
     * Show empty category/taxonomy
     * @var bool
     */
    protected $hide_empty  = false;

    /**
     * Show hierarchy
     * @var bool
     */
    protected $hierarchy = false;

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
            $this->id_base = 'listar_menu';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Listing Category', 'listar');
        }

        $this->post_type = Listar::$post_type . '_category';

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

        if (!empty($instance['title'])) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
        }

        if($this->dropdown) {
            $args = [
                'show_option_all' => __('Select option', 'listar'),
                'class' => 'form-select',
                'taxonomy' => $this->post_type,
                'show_count' => $this->count,
                'hide_if_empty' => $this->hide_empty,
                'hierarchical' => $this->hierarchy
            ];

            if($this->top_level) {
                $args['parent'] = 0;
            }

            if($this->hierarchy && isset($args['parent'])) {
                unset($args['parent']);
            }

            wp_dropdown_categories($args);

        } else {
            $args = [
                'title_li' => '',
                'hide_empty' => $this->hide_empty,
                'taxonomy' => $this->post_type,
                'depth' => -1,
                'walker' => new Sidebar_Menu([
                    'count' => $this->count
                ])
            ];

            if($this->top_level) {
                $args['parent'] = 0;
            }

            if($this->hierarchy) {
                unset($args['parent']);
                unset($args['depth']);
            }

            wp_list_categories($args);
        }

        echo wp_kses_post($this->after_widget);
    }

    /**
     * Widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
            <input type="text"
                   class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><b><?php _e('Description', 'listar'); ?></b></label>
            <textarea class="widefat"
                      id="<?php echo esc_attr($this->get_field_id('desc')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('desc')); ?>"
                      rows="3"> <?php echo isset($instance['desc']) ? esc_html($instance['desc']) : ''; ?></textarea>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('dropdown')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('dropdown')); ?>"
                   value="1" <?php echo isset($instance['dropdown']) && absint($instance['dropdown']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('dropdown')); ?>"><?php _e('Display as dropdown', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>"
                   value="1" <?php echo isset($instance['count']) && absint($instance['count']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>"><?php _e('Show counts', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('top_level')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('top_level')); ?>"
                   value="1" <?php echo isset($instance['top_level']) && absint($instance['top_level']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('top_level')); ?>"><?php _e('Show only top level', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hide_empty')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_empty')); ?>"
                   value="1" <?php echo isset($instance['hide_empty']) && absint($instance['hide_empty']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hide_empty')); ?>"><?php _e('Hide empty count', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hierarchy')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hierarchy')); ?>"
                   value="1" <?php echo isset($instance['hierarchy']) && absint($instance['hierarchy']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hierarchy')); ?>"><?php _e('Show hierarchy', 'listar'); ?></label>
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
            'dropdown' => (!empty($new_instance['dropdown'])) ? absint($new_instance['dropdown']) : 0,
            'count' => (!empty($new_instance['count'])) ? $new_instance['count'] : 0,
            'top_level' => (!empty($new_instance['top_level'])) ? $new_instance['top_level'] : 0,
            'hide_empty' => (!empty($new_instance['hide_empty'])) ? $new_instance['hide_empty'] : 0,
            'hierarchy' => (!empty($new_instance['hierarchy'])) ? $new_instance['hierarchy'] : 0
        ];

        return $instance;
    }
}

?>
