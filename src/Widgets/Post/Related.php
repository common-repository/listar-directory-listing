<?php
namespace ListarWP\Plugin\Widgets\Post;
use ListarWP\Plugin\Listar;
use WP_Widget;
use WP_Query;

class Related extends WP_Widget
{
    /**
     * Default display number
     * @var int
     */
    protected $display_number = 8;

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
            $this->id_base = 'listar_post';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Related Post', 'listar');
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

        // Query condition
        $wp_args = [
            'post_status' => 'publish',
            'posts_per_page' => absint($this->display_number)
        ];

        if(is_single()) {
            global $post;
            $wp_args['tax_query'] = [
                'relation' => 'OR',
                [
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => wp_get_post_terms($post->ID, 'category', ['fields' => 'ids'])
                ],
            ];

            $tags = get_the_tags($post->ID);
            if(!empty($tags)) {
                $wp_args[] = [
                    'taxonomy' => 'tag',
                    'field' => 'term_id',
                    'terms' => array_column($tags, 'term_id')
                ];
            }

            // Exclude current post
            $wp_args['post__not_in'] = [$post->ID];
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

        echo wp_kses_post($this->before_widget);

        if (isset($instance['title']) && !empty($instance['title'])) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
        }

        if($this->sidebar_id === 'listar-blog-sidebar') {
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    locate_template('template-parts/post/content-thumb.php', true, false);
                }
            }
        } else {
            if ($query->have_posts()) {
                echo '<div class="row row-cols-1 row-cols-xs-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-4 g-4 py-4">';
                while ($query->have_posts()) {
                    $query->the_post();
                    locate_template('template-parts/post/content-excerpt.php', true, false);
                }
                echo '</div>';
            }
        }

        echo wp_kses_post($this->after_widget);

        wp_reset_postdata();
    }

    /**
     * Widget form handler
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        $sort_options = listar_get_listing_sort_option();
        array_pop($sort_options);

        $sort_option = isset($instance['sort_option']) ? esc_attr($instance['sort_option']) : '';
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
        $instance['display_number'] = (!empty($new_instance['display_number'])) ? absint($new_instance['display_number']) : $this->display_number;
        $instance['sort_option'] = (!empty($new_instance['sort_option'])) ? sanitize_textarea_field($new_instance['sort_option']) : '';

        return $instance;
    }
}
?>
