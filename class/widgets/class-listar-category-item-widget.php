<?php
use ListarWP\Plugin\Listar;

class Listar_Category_Item_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'listar_category_item',  // Base ID
            __('[Listar] Category Item', 'listar')   // Name
        );
    }

    public $args = array(
        'before_title'  => '<h1 class="title-header">',
        'after_title'   => '</h1>',
        'before_widget' => '<div class="row"><div class="list-services">',
        'after_widget'  => '</div></div>'
    );

    /**
     * Widget render html/content
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $terms = [];
        if (isset($instance['categories']) && is_array($instance['categories']) && !empty($instance['categories'])) {
            $terms = get_terms([
                'taxonomy' => Listar::$post_type . '_category',
                'include' => $instance['categories'],
                'hide_empty'  => FALSE,
            ]);
        }

        $menu = '';
        $more = '';

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $meta_data = get_term_meta($term->term_id, '', TRUE);
                $meta_data = listar_convert_single_value($meta_data);

                $menu .= '<a class="icon-service" href="'.listar_get_listing_url(['category' => $term->term_id]).'">';
                $menu .= '    <div class="icon rounded-circle" style="background-color:' . esc_attr($meta_data['color']) . '">';
                $menu .= '        <i class="' . esc_attr($meta_data['icon']) . '"></i>';
                $menu .= '    </div>';
                $menu .= '    <div class="text pt-2">';
                $menu .= '        <span>' . esc_attr($term->name) . '</span>';
                $menu .= '    </div>';
                $menu .= '</a>';
            }
        }

        if (isset($instance['more_mode']) && absint($instance['more_mode']) === 1) {
            $more .= '<a class="icon-service" href="'.listar_get_listing_url().'">';
            $more .= '    <div class="icon rounded-circle" style="background-color:#FF8A65">';
            $more .= '        <i class="fa fa-ellipsis-v"></i>';
            $more .= '    </div>';
            $more .= '    <div class="text pt-2">';
            $more .= '        <span>'.__('More', 'listar').'</span>';
            $more .= '    </div>';
            $more .= '</a>';
        }

        echo '<div class="container">';
        if (!empty($instance['title']) && !$instance['hide_title']) {
            echo $this->args['before_title'] . apply_filters('widget_title', $instance['title']) . $this->args['after_title'];
            if (isset($instance['desc']) && !empty($instance['desc'])) {
                echo '<p class="desc-header">' . $instance['desc'] . '</p>';
            }
        }

        echo $this->args['before_widget'];

        echo $menu;
        echo $more;

        echo $this->args['after_widget'];
        echo '</div>';
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
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('categories')); ?>"><?php echo esc_attr('Categories:', 'listar'); ?></label><br />
            <?php
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    if (isset($instance['categories']) && is_array($instance['categories']) && in_array($term->term_id, $instance['categories'])) {
                        $checked = ' checked="true"';
                    } else {
                        $checked = null;
                    }
            ?>
                    <input type="checkbox" id="<?php echo $this->get_field_id('categories') . '_' . $term->term_id; ?>" name="<?php echo $this->get_field_name('categories'); ?>[]" value="<?php echo $term->term_id; ?>" <?php echo $checked; ?> />
                    <label for="<?php echo $this->get_field_id('categories') . '_' . $term->term_id; ?>">
                        <?php echo $term->name; ?>
                    </label>
                    <br />
            <?php
                }
            }
            ?>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('hide_title')); ?>"><?php echo esc_attr('Hide Title:', 'listar'); ?></label><br />
            <input type="checkbox" id="<?php echo $this->get_field_id('hide_title'); ?>" name="<?php echo $this->get_field_name('hide_title'); ?>" value="1" <?php echo isset($instance['hide_title']) && absint($instance['hide_title']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo $this->get_field_id('hide_title'); ?>">Set hide/unhide for widget title for display on frontend.</label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('more_mode')); ?>">
                <?php echo esc_attr('Display More:', 'more_mode'); ?>
            </label><br />
            <input type="checkbox" id="<?php echo $this->get_field_id('more_mode'); ?>" name="<?php echo $this->get_field_name('more_mode'); ?>" value="1" <?php echo isset($instance['more_mode']) && absint($instance['more_mode']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo $this->get_field_id('more_mode'); ?>">Set display more menu.</label>
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
        $instance['hide_title'] = (!empty($new_instance['hide_title'])) ? absint($new_instance['hide_title']) : 0;
        $instance['categories'] = (!empty($new_instance['categories'])) ? $new_instance['categories'] : [];
        $instance['more_mode'] = (!empty($new_instance['more_mode'])) ? $new_instance['more_mode'] : 0;

        return $instance;
    }
}
?>
