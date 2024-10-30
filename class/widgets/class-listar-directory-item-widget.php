<?php //recent location
use ListarWP\Plugin\Listar;
class Listar_Directory_Item_Widget extends WP_Widget
{
    /**
     * Default display number
     * @var int
     */
    private $_display_number = 6;

    public function __construct()
    {
        parent::__construct(
            'listar_directory_item',  // Base ID
            __('[Listar] Directory Item', 'listar')   // Name
        );
    }

    public $args = array(
        'before_title'  => '<h1 class="title-header">',
        'after_title'   => '</h1>',
        'before_widget' => '<div class="container">
                                <div class="popular-locations">',
        'after_widget'  => '    </div>
                            </div>'
    );

    /**
     * Widget render html/content
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $args = [
            'post_type' => Listar::$post_type,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order'   => 'DESC',
            'posts_per_page' => isset($instance['display_number']) ? absint($instance['display_number']) : $this->_display_number
        ];
        $query = new WP_Query($args);

        echo $this->args['before_widget'];

        if (!empty($instance['title'])) {
            echo $this->args['before_title'] . apply_filters('widget_title', $instance['title']) . $this->args['after_title'];
        }

        echo '<p class="desc-header">';
        echo esc_attr($instance['text'], 'listar');
        echo '</p>';
        echo '<div class="place-grid">';
        echo '    <ul class="list-unstyled grid-view">';
        if($query->have_posts()) {
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<li>';
                get_template_part('template-parts/listar/content', 'item');
                echo '</li>';
            }
        }
        echo '    </ul>';
        echo '</div>';
        echo '<div class="btn-view-all row">';
        echo '    <a class="btn btn-primary" href="'.get_bloginfo('url').'?post_type=listar">'.__('View All', 'listar').'</a>';
        echo '</div>';

        echo $this->args['after_widget'];

        wp_reset_postdata();
    }

    /**
     * Widget form handler
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_attr('Title:', 'listar'); ?></label>
            <input type="text"
                   class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>"
            />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><?php echo esc_attr('Description:', 'listar'); ?></label>
            <textarea class="widefat"
                      id="<?php echo esc_attr($this->get_field_id('desc')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('desc')); ?>"
                      rows="5"
            > <?php echo isset($instance['desc']) ? esc_attr($instance['desc']) : ''; ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('display_number')); ?>"><?php echo esc_attr('Total Items:', 'listar'); ?></label>
            <input type="number"
                   class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('display_number')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('display_number')); ?>"
                   value="<?php echo isset($instance['display_number']) ? esc_attr($instance['display_number']) : $this->_display_number; ?>"
            />
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
        $instance['text'] = (!empty($new_instance['text'])) ? trim(sanitize_textarea_field($new_instance['text'])) : '';
        $instance['display_number'] = (!empty($new_instance['display_number'])) ? absint($new_instance['display_number']) : $this->_display_number;

        return $instance;
    }
}
?>
