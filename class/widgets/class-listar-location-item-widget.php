<?php // popular location
use ListarWP\Plugin\Listar;
class Listar_Location_Item_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'listar_location_item',  // Base ID
            __('[Listar] Location Item', 'listar')   // Name
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

    public function widget($args, $instance)
    {
        $terms = [];
        if (isset($instance['locations']) && is_array($instance['locations']) && !empty($instance['locations'])) {
            $terms = get_terms([
                'taxonomy' => Listar::$post_type . '_location',
                'include' => $instance['locations'],
                'hide_empty'  => TRUE,
            ]);
        }

        $html_content = '';

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $meta_data = get_term_meta($term->term_id, '', TRUE);
                $meta_data = listar_convert_single_value($meta_data);
                $url_image = isset($meta_data['featured_image']) && !empty($meta_data['featured_image']) ? wp_get_attachment_url($meta_data['featured_image']) : '';

                $html_content .= '<div class="card">';
                $html_content .= '    <div class="popular-location">';
                $html_content .= '        <img src="' . $url_image . '" />';
                $html_content .= '        <div class="gradient"></div>';
                $html_content .= '        <div class="item-background-info">';
                $html_content .= '            <div class="pd-b-10">';
                $html_content .= '                <a class="title text-white" href="' . listar_get_listing_url(['location' => $term->term_id]) . '">' . esc_attr($term->name) . '</a>';
                $html_content .= '            </div>';
                $html_content .= '            <div class="desc">';
                $html_content .= '                <p>' . esc_attr($term->description) . '</p>';
                $html_content .= '            </div>';
                $html_content .= '        </div>';
                $html_content .= '    </div>';
                $html_content .= '</div>';
            }
        }

        echo $this->args['before_widget'];

        if (!empty($instance['title'])) {
            echo $this->args['before_title'] . apply_filters('widget_title', $instance['title']) . $this->args['after_title'];
        }

        if (isset($instance['desc']) && !empty($instance['desc'])) {
            echo '<p class="desc-header">' . $instance['desc'] . '</p>';
        }

        echo '<div class="list-grid-scroll location-gallery scrolling-wrapper">';
        echo $html_content;
        echo '</div>';

        echo $this->args['after_widget'];
    }

    public function form($instance)
    {
        $terms = get_terms(Listar::$post_type . '_location');
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
            <label for="<?php echo esc_attr($this->get_field_id('locations')); ?>"><?php echo esc_attr('Locations:', 'listar'); ?></label><br />
            <?php
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    if (isset($instance['locations']) && is_array($instance['locations']) && in_array($term->term_id, $instance['locations'])) {
                        $checked = ' checked="true"';
                    } else {
                        $checked = null;
                    }
            ?>
                    <input type="checkbox" id="<?php echo $this->get_field_id('locations') . '_' . $term->term_id; ?>" name="<?php echo $this->get_field_name('locations'); ?>[]" value="<?php echo $term->term_id; ?>" <?php echo $checked; ?> />
                    <label for="<?php echo $this->get_field_id('locations') . '_' . $term->term_id; ?>">
                        <?php echo $term->name; ?>
                    </label>
                    <br />
            <?php
                }
            }
            ?>
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
        $instance['locations'] = (!empty($new_instance['locations'])) ? $new_instance['locations'] : [];

        return $instance;
    }
}
?>
