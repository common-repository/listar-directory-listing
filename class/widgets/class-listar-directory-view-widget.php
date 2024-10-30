<?php
use ListarWP\Plugin\Listar;
/**
 * Class Listar_Directory_View_Widget
 * - Get data post base on current query post ID
 */
class Listar_Directory_View_Widget extends WP_Widget
{
    /**
     * Default display number
     * @var int
     */
    private $_display_number = 6;

    public function __construct()
    {
        parent::__construct(
            'listar_directory_view_right',  // Base ID
            __('[Listar] Directory View', 'listar')   // Name
        );
    }

    public $args = array(
        'before_title'  => '<h3 class=\'grid-title\'>',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="pd-20 bg-main-content mg-t-20 bd-r-main">
            <div class="recent-category">',
        'after_widget'  => '    </div>
                            </div>'
    );

    /**
     * Widget render html/content
     * - Get related post by categories
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $categories = wp_get_post_terms(get_the_ID(), Listar::$post_type.'_category');
        $term_ids = [];
        array_walk($categories, function ($term, $key) use (&$term_ids){
            $term_ids[] = $term->term_id;
        });

        $args = [
            'tax_query' => [
                [
                    'taxonomy' => Listar::$post_type.'_category',
                    'field'    => 'term_id',
                    'terms'    => $term_ids
                ]
            ],
            'post__not_in' => [get_the_ID()],
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

        echo '<ul class=\'list-unstyled recent-category-list\'>';
        if($query->have_posts()) {
            while ( $query->have_posts() ) {
                $query->the_post();
                echo "<li><a href='".esc_url( get_permalink() )."'>";
                get_template_part('template-parts/listar/content', 'thumb');
                echo "</a></li>";
            }
        }
        echo '</ul>';
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
        $instance['display_number'] = (!empty($new_instance['display_number'])) ? absint($new_instance['display_number']) : $this->_display_number;

        return $instance;
    }
}
?>
