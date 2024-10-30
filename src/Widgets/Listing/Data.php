<?php
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Place_Model;
use WP_Widget;
use WP_Query;

class Data extends WP_Widget
{
    /**
     * Default display number
     * @var int
     */
    protected $display_number = 8;

    /**
     * Direction format
     * - horizontal : ==
     * - vertical : ||
     * @var string
     */
    protected $direction = 'vertical';

    /**
     * Layout
     * - list/grid/block
     * @var string
     */
    protected $layout = 'list';

    /**
     * Sidebar id
     * @var string
     */
    protected $sidebar_id = '';

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
     * Init
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
            $this->id_base = 'listar_data';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Listing Data', 'listar');
        }

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
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        if(isset($args['id'])) {
            $this->sidebar_id = $args['id'];
        }

        $this->initialize($args);
        $this->initialize($instance);

        $query = self::query($instance);

        echo wp_kses_post($this->before_widget);

        if (isset($instance['title']) && !empty($instance['title'])) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
        }

        if($this->sidebar_id === 'listar-listing-right-sidebar') {
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    locate_template('template-parts/listar/content-thumb.php', true, false);
                }
            }
        } else {
            if ($query->have_posts()) {
                echo '<div class="row row-cols-1 row-cols-xs-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-4 g-4 py-4">';
                while ($query->have_posts()) {
                    $query->the_post();
                    locate_template('template-parts/listar/content-excerpt.php', true, false);
                }
                echo '</div>';
            }
        }

        echo wp_kses_post($this->after_widget);

        wp_reset_postdata();
    }

    /**
     * Get data for mobile
     */
    public static function json($instance = [])
    {
        $query = self::query($instance);
        $data  = $query->get_posts();

        if(is_array($data) && !empty($data)) {
            // Set user props
            $user = wp_get_current_user();
            if(!empty((array)$user->data)) {
                Place_Model::$user = $user->data;
            }
            foreach($data as &$post) {
                Place_Model::assign_data_list($post);
            }
        }
        
        return [
            'title' => isset($instance['title']) ? $instance['title'] : '',
            'description' => isset($instance['description']) ? $instance['description'] : '',
            'direction' => isset($instance['direction']) ? $instance['direction'] : '',
            'layout' => isset($instance['layout']) ? $instance['layout'] : '',
            'type' => 'listing',
            'data' => $data
        ];
    }

    /**
     * Widget form handler
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        $sort_options = listar_get_listing_sort_option();
        $sort_option = isset($instance['sort_option']) ? esc_attr($instance['sort_option']) : '';
        $direction = isset($instance['direction']) ? esc_attr($instance['direction']) : $this->direction;
        $layout = isset($instance['layout']) ? esc_attr($instance['layout']) : $this->layout;
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
            <label for="<?php echo esc_attr($this->get_field_id('description')); ?>"><b><?php _e('Description', 'listar'); ?></b></label>
            <input type="text"
                   class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('description')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('description')); ?>"
                   value="<?php echo isset($instance['description']) ? esc_attr($instance['description']) : ''; ?>"
            />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('display_number')); ?>"><b><?php _e('Total Items', 'listar'); ?></b></label>
            <input type="number"
                   class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('display_number')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('display_number')); ?>"
                   value="<?php echo isset($instance['display_number']) ? esc_attr($instance['display_number']) : $this->display_number; ?>"
            />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('sort_option')); ?>"><b><?php _e('Order by', 'listar'); ?></b></label>
            <select id="<?php echo esc_attr($this->get_field_id('sort_option')); ?>" name="<?php echo esc_attr($this->get_field_name('sort_option')); ?>">
                <option <?php echo esc_attr($sort_option == '') ? "selected='selected'" : '';?> value=""><?php _e('Select', 'listar');?></option>
                <?php foreach($sort_options as $sort) { ?>
                    <option <?php echo esc_attr($sort_option) == esc_attr(json_encode($sort['option'])) ? "selected='selected'" : '';?>
                            value="<?php echo esc_attr(json_encode($sort['option']));?>">
                        <?php echo esc_html($sort['title']);?>
                    </option>
                <?php } ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('direction')); ?>">
                <b><?php _e('Mobile Direction', 'listar'); ?></b>
            </label><br/>
            <label for="<?php echo esc_attr($this->get_field_id('direction')); ?>"
                   style="padding-right: 20px"
            >
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('direction')); ?>"
                       value="vertical"
                    <?php echo esc_attr($direction === 'vertical') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('Vertical', 'listar'); ?>
            </label>
            <label for="<?php echo esc_attr($this->get_field_id('direction')); ?>">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('direction')); ?>"
                       value="horizontal"
                        <?php echo esc_attr($direction === 'horizontal') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('Horizontal', 'listar'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>">
                <b><?php _e('Mobile Layout', 'listar'); ?></b>
            </label><br/>
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>" style="padding-right: 20px">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('layout')); ?>"
                       value="list"
                    <?php echo esc_attr($layout === 'list') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('List', 'listar'); ?>
            </label>
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>"  style="padding-right: 20px">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('layout')); ?>"
                       value="grid"
                    <?php echo esc_attr($layout === 'grid') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('Grid', 'listar'); ?>
            </label>
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('layout')); ?>"
                       value="block"
                    <?php echo esc_attr($layout === 'block') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('Block', 'listar'); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Widget form action handler
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '';
        $instance['description'] = (!empty($new_instance['description'])) ? trim(sanitize_textarea_field($new_instance['description'])) : '';
        $instance['display_number'] = (!empty($new_instance['display_number'])) ? absint($new_instance['display_number']) : $this->display_number;
        $instance['sort_option'] = (!empty($new_instance['sort_option'])) ? sanitize_textarea_field($new_instance['sort_option']) : '';
        $instance['direction'] = (!empty($new_instance['direction'])) ? sanitize_textarea_field($new_instance['direction']) : $this->direction;
        $instance['layout'] = (!empty($new_instance['layout'])) ? sanitize_textarea_field($new_instance['layout']) : $this->layout;

        return $instance;
    }

    /**
     * Get query db 
     * @param array $instance 
     * @return WP_Query
     */
    public static function query($instance = [])
    {
        // Query condition
        $wp_args = [
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => absint($instance['display_number'])
        ];

        if(is_single()) {
            global $post; // Current post

            // Similar taxonomy
            if (isset($instance['taxonomy']) && !empty($instance['taxonomy'])) {
                $post_terms = wp_get_post_terms($post->ID, $instance['taxonomy'], ['fields' => 'ids']);
                if (!empty($post_terms)) {
                    $wp_args['tax_query'][] = [
                        'taxonomy' => $instance['taxonomy'],
                        'field' => 'term_id',
                        'terms' => $post_terms
                    ];
                }
            }

            // Exclude current post
            $wp_args['post__not_in'] = [$post->ID];
        }

        // Terms : Location (Country/City/State)
        if(isset($instance['location']) && $instance['location'] != '') {
            $wp_args['tax_query'][] = [
                'taxonomy' => Listar::$post_type.'_location',
                'field'    => 'term_id',
                'terms'    => is_array($instance['location']) ? $instance['location'] : absint($instance['location'])
            ];
        }

        // Terms : Category
        if(isset($instance['category']) && $instance['category'] != '') {
            $wp_args['tax_query'][] = [
                'taxonomy' => Listar::$post_type.'category',
                'field'    => 'term_id',
                'terms'    => is_array($instance['category']) ? $instance['category'] : absint($instance['category'])
            ];
        }

        // Sort option
        if(isset($instance['sort_option']) && !empty($instance['sort_option'])) {
            $sort_option = json_decode($instance['sort_option'], TRUE);

            if(is_array($sort_option) && !empty($sort_option)) {
                foreach($sort_option as $key => $value) {
                    $wp_args[$key] = esc_attr($value);
                }

            }
        } else {
            $wp_args['orderby'] = 'date';
            $wp_args['order'] = 'DESC';
        }
        
        $query = new WP_Query($wp_args);
        return $query;
    }
}
?>
