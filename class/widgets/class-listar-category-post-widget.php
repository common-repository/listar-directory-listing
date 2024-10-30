<?php
use ListarWP\Plugin\Listar;
class Listar_Category_Post_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'listar_category_post',  // Base ID
            __('[Listar] Category Post', 'listar')   // Name
        );
    }

    public $args = array(
        'before_title'  => '<div class="grid-title pd-b-20">',
        'after_title'   => '</div>',
        'before_widget' => '<ul class="list-unstyled recent-category-list">',
        'after_widget'  => '</ul>'
    );

    /**
     * Widget render html/content
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $categories = get_categories(array(
            'orderby' => 'name',
            'order'   => 'ASC'
        ));

        $category_html = '';

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $category_html .= '<li class="mg-b-10">';
                $category_html .= '    <a href="' . esc_url(get_category_link($category->term_id)) . '" class="blog-list-category d-flex justify-content-between align-items-center" alt="' . esc_attr($category->name) . '">';
                $category_html .= '        <span class="title mg-0 text-ellipsis">' . esc_html($category->name) . '</span>';
                $category_html .= '        <span class="count-number wd-30 badge">' . esc_html($category->category_count) . '</span>';
                $category_html .= '    </a>';
                $category_html .= '</li>';
            }
        }

        if (!empty($instance['title'])) {
            echo $this->args['before_title'] . apply_filters('widget_title', $instance['title']) . $this->args['after_title'];
            if (isset($instance['desc']) && !empty($instance['desc'])) {
                echo '<p class="desc-header">' . $instance['desc'] . '</p>';
            }
        }

        echo $this->args['before_widget'];
        echo $category_html;
        echo $this->args['after_widget'];
    }

    /**
     * Widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        $terms = get_terms(Listar::$post_type . '_category');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_attr('Title:', 'listar'); ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><?php echo esc_attr('Description:', 'listar'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('desc')); ?>" name="<?php echo esc_attr($this->get_field_name('desc')); ?>" rows="5"> <?php echo isset($instance['desc']) ? esc_attr($instance['desc']) : ''; ?></textarea>
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
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '';
        $instance['desc'] = (!empty($new_instance['desc'])) ? trim(sanitize_textarea_field($new_instance['desc'])) : '';

        return $instance;
    }
}
?>
