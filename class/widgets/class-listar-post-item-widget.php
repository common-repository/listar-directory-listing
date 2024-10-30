<?php
use ListarWP\Plugin\Listar;
class Listar_Post_Item_Widget extends WP_Widget
{
    /**
     * Default display number
     * @var int
     */
    private $_display_number = 3;

    public function __construct()
    {
        parent::__construct(
            'listar_post_item',  // Base ID
            __('[Listar] Post Item', 'listar')   // Name
        );
    }

    public $args = [
        'before_title'  => '<h1 class="title-header">',
        'after_title'   => '</h1>',
        'before_widget' => '<div class="list-blogs">
                                <div class="container">
                                    <div class="popular-locations">',
        'after_widget'  => '        </div>
                                </div>
                            </div>'
    ];

    /**
     * Widget render html content
     * @param array $args
     * @param array $instance
     * @throws Exception
     */
    public function widget($args, $instance)
    {
        $args = [
            'post_type'        => 'post',
            'category'         => 0,
            'post_status'      => 'publish',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'posts_per_page'   => isset($instance['display_number']) ? absint($instance['display_number']) : $this->_display_number,
        ];

        echo $this->args['before_widget'];

        if (!empty($instance['title'])) {
            echo $this->args['before_title'] . apply_filters('widget_title', $instance['title']) . $this->args['after_title'];
        }

        if (isset($instance['desc']) && !empty($instance['desc'])) {
            echo '<p class="desc-header">' . $instance['desc'] . '</p>';
        }

        echo '<div class="d-flex grid-wrap recent-blogs">';
        $query  = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/post/content', 'item');
            }
        }
        echo '</div>';
        echo '<div class="btn-view-all row">';
        if(isset($instance['view_all_url']) && $instance['view_all_url'] != '') {
            echo '<a class="btn btn-primary" href="' . $instance['view_all_url'] . '">' . __('View All', 'listar') . '</a>';
        }
        echo '</div>';

        echo $this->args['after_widget'];

        wp_reset_postdata();
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
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_attr('Title:', 'listar'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : '' ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><?php echo esc_attr('Description:', 'listar'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('desc')); ?>" name="<?php echo esc_attr($this->get_field_name('desc')); ?>" rows="5"> <?php echo isset($instance['desc']) ? esc_attr($instance['desc']) : ''; ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('display_number')); ?>"><?php echo esc_attr('Total Items:', 'listar'); ?></label>
            <input type="number" class="widefat" id="<?php echo esc_attr($this->get_field_id('display_number')); ?>" name="<?php echo esc_attr($this->get_field_name('display_number')); ?>" value="<?php echo isset($instance['display_number']) ? esc_attr($instance['display_number']) : $this->_display_number; ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('view_all_url')); ?>"><?php echo esc_attr('View Page URL:', 'listar'); ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('view_all_url')); ?>" name="<?php echo esc_attr($this->get_field_name('view_all_url')); ?>" value="<?php echo isset($instance['view_all_url']) ? esc_attr($instance['view_all_url']) : ''; ?>" />
        </p>
<?php
    }

    /**
     * Widget update action
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];

        $instance['title'] = (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '';
        $instance['desc'] = (!empty($new_instance['desc'])) ? trim(sanitize_textarea_field($new_instance['desc'])) : '';
        $instance['display_number'] = (!empty($new_instance['display_number'])) ? $new_instance['display_number'] : $this->_display_number;
        $instance['view_all_url'] = (!empty($new_instance['view_all_url'])) ? trim(sanitize_textarea_field($new_instance['view_all_url'])) : '';

        return $instance;
    }
}
?>
