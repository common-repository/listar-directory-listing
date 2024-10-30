<?php
namespace ListarWP\Plugin\Widgets\Post;
use WP_Widget;
use WP_Query;
use Exception;

class Recent extends WP_Widget
{
    /**
     * Default display number
     * @var int
     */
    private $_display_number = 3;

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

    public function __construct($args = [])
    {
        parent::__construct(
            'listar_post_recent',  // Base ID
            __('[Listar] Recent Post', 'listar')   // Name
        );
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
     * Widget render html content
     * @param array $args
     * @param array $instance
     * @throws Exception
     */
    public function widget($args = [], $instance = [])
    {
        $query  = new WP_Query([
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'posts_per_page'   => isset($instance['display_number']) ? absint($instance['display_number']) : $this->_display_number,
        ]);

        if(isset($args['id'])) {
            $this->sidebar_id = $args['id'];
        }
        $this->initialize($args);
        $this->initialize($instance);

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
     * Widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
    ?>
<p>
    <label
        for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
    <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
        name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
        value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : '' ?>" />
</p>
<p>
    <label
        for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><b><?php _e('Description', 'listar'); ?></b></label>
    <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('desc')); ?>"
        name="<?php echo esc_attr($this->get_field_name('desc')); ?>"
        rows="5"> <?php echo isset($instance['desc']) ? esc_html($instance['desc']) : ''; ?></textarea>
</p>
<p>
    <label
        for="<?php echo esc_attr($this->get_field_id('display_number')); ?>"><b><?php _e('Total Items', 'listar'); ?></b></label>
    <input type="number" class="widefat" id="<?php echo esc_attr($this->get_field_id('display_number')); ?>"
        name="<?php echo esc_attr($this->get_field_name('display_number')); ?>"
        value="<?php echo isset($instance['display_number']) ? esc_attr($instance['display_number']) : $this->_display_number; ?>" />
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

        return $instance;
    }
}
?>
