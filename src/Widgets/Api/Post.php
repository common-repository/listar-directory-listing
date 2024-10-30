<?php
namespace ListarWP\Plugin\Widgets\Api;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Post_Model;
use WP_Widget;
use WP_Query;

class Post extends WP_Widget
{
    /**
     * Post type
     * @var string
     */
    static $post_type = 'post';

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
            $this->id_base = 'listar_api_post';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Api Post', 'listar');
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
        $this->initialize($args);
        $this->initialize($instance);

        $query = self::query($instance);

        if(isset($args['id'])) {
            $this->sidebar_id = $args['id'];
        }

        echo wp_kses_post($this->before_widget);

        if ($query->have_posts()) {

            if (isset($instance['title']) && !empty($instance['title'])) {
                echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
            }

            if (isset($instance['desc']) && !empty($instance['desc'])) {
                echo '<p class="text-muted">' . $instance['desc'] . '</p>';
            }

            if($this->sidebar_id == 'listar-blog-sidebar') {
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        locate_template('template-parts/post/content-thumb.php', true, false);
                    }
                }
            } else {
                if ($query->have_posts()) {
                    echo '<div class="row row-cols-1 row-cols-xs-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-4 g-4">';
                    while ($query->have_posts()) {
                        $query->the_post();
                        locate_template('template-parts/post/content-excerpt.php', true, false);
                    }
                    echo '</div>';
                }
            }
        }

        echo wp_kses_post($this->after_widget);

        wp_reset_postdata();
    }

    /**
     * Get data for mobile
     * @param array $instance 
     */
    public static function json($instance = [])
    {   
        $query = self::query($instance);
        $data  = $query->get_posts();        
        if(is_array($data) && !empty($data)) {
            foreach($data as &$post) {
                unset($post->post_content);
                Post_Model::assign_data_list($post);
            }
        }

        return [
            'title' => isset($instance['title']) ? $instance['title'] : '',
            'description' => isset($instance['description']) ? $instance['description'] : '',
            'direction' => isset($instance['direction']) ? $instance['direction'] : '',
            'layout' => isset($instance['layout']) ? $instance['layout'] : '',
            'type' => 'post',
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
        <div><!-- Categories -->
            <label for="<?php echo esc_attr($this->get_field_id('categories')); ?>">
                <b><?php _e('Select item will be displayed', 'listar'); ?></b>
            </label>
            <div style="overflow: scroll; padding-left: 10px; height: 200px">
                <?php
                $terms = get_terms([
                    'taxonomy'   => 'category',
                    'hide_empty' => false,
                ]);
                if (!empty($terms)) {
                    foreach ($terms as $term) {
                        if (isset($instance['categories']) && is_array($instance['categories']) && in_array($term->term_id, $instance['categories'])) {
                            $checked = ' checked="true"';
                        } else {
                            $checked = null;
                        }
                        if($term->parent === 0) {
                            ?>
                            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $term->term_id; ?>"
                                   name="<?php echo esc_attr($this->get_field_name('categories')); ?>[]"
                                   value="<?php echo esc_attr($term->term_id); ?>" <?php echo esc_attr($checked); ?> />
                            <label for="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $term->term_id; ?>">
                                <?php echo esc_html($term->name); ?>
                            </label>
                            <br />
                            <?php
                            foreach( $terms as $sub_term) {
                                if($sub_term->parent === $term->term_id) {
                                    if (isset($instance['categories']) && is_array($instance['categories']) && in_array($sub_term->term_id, $instance['categories'])) {
                                        $checked = ' checked="true"';
                                    } else {
                                        $checked = null;
                                    }
                                    ?>
                                    <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $sub_term->term_id; ?>"
                                           name="<?php echo esc_attr($this->get_field_name('categories')); ?>[]"
                                           value="<?php echo esc_attr($sub_term->term_id); ?>" <?php echo esc_attr($checked); ?> />
                                    <label for="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $sub_term->term_id; ?>">
                                        -- <?php echo esc_html($sub_term->name); ?>
                                    </label>
                                    <br />
                                    <?php
                                }
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
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
        return [
            'title' => (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '',
            'description' => (!empty($new_instance['description'])) ? trim(sanitize_textarea_field($new_instance['description'])) : '',
            'display_number' => (!empty($new_instance['display_number'])) ? absint($new_instance['display_number']) : $this->display_number,
            'sort_option' => (!empty($new_instance['sort_option'])) ? sanitize_textarea_field($new_instance['sort_option']) : '',
            'direction' => (!empty($new_instance['direction'])) ? sanitize_textarea_field($new_instance['direction']) : $this->direction,
            'layout' => (!empty($new_instance['layout'])) ? sanitize_textarea_field($new_instance['layout']) : $this->layout,
            'categories' => (!empty($new_instance['categories'])) ? $new_instance['categories'] : [],
        ];
    }

    /**
     * Get data base on $instance 
     * @param array $instance 
     * @return WP_Query
     */
    public static function query($instance = [])
    {
        $wp_args = [
            'post_type' => self::$post_type,
            'post_status' => 'publish',
            'posts_per_page' => absint($instance['display_number'])
        ];

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

        // Category
        if(isset($instance['categories']) && !empty($instance['categories'])) {
            $wp_args['category__in'] = $instance['categories'];
        }

        $query = new WP_Query($wp_args);
        return $query;
    }
}
?>
