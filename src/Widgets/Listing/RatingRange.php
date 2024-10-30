<?php
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use WP_Widget;

class RatingRange extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'listar_rating_range',  // Base ID
            __('[Listar] Listing Rating Range', 'listar')   // Name
        );
    }

    public $args = array(
        'before_title'  => '<h5>',
        'after_title'   => '</h5>',
        'before_widget' => '<aside class="mt-4">',
        'after_widget'  => '</aside>'
    );

    /**
     * Widget render html/content
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        echo wp_kses_post($this->args['before_widget']);

        if (!empty($instance['title'])) {
            echo wp_kses_post($this->args['before_title']) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->args['after_title']);
        }

        ?>
        <div class="d-flex flex-column g-3 listar-rating-menu">
            <?php
            $rate = 5;
            while($rate > 0) {
                $not_rate = 5 - $rate;
            ?>
                <a href="<?php echo esc_url(add_query_arg('rating', $rate));?>"
                   class="d-flex justify-content-between align-items-center my-1">
                    <div class="d-flex g-3 me-2">
                        <?php for($i=1; $i <= $rate; $i++) { ?>
                        <i class="fas fa-star text-warning fs-6 me-1"></i>
                        <?php } ?>
                        <?php for($i=1; $i <= $not_rate; $i++) { ?>
                            <i class="fas fa-star text-black-50 fs-6 me-1"></i>
                        <?php } ?>
                    </div>
                    <span><?php echo esc_attr($rate).' '.__('Start', 'listar');?> </span>
                </a>
            <?php
                $rate--;
            }
            ?>
        </div>
        <?php

        echo wp_kses_post($this->args['after_widget']);
    }

    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>" />
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

        return $instance;
    }
}
?>
