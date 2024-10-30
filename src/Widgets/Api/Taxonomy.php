<?php
/**
 * Make json data for mobile widgets
 * - Circle
 * - Square
 * - Icon
 */
namespace ListarWP\Plugin\Widgets\Api;
use WP_Widget;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Category_Model;
use ListarWP\Plugin\Models\Location_Model;

class Taxonomy extends WP_Widget
{
    /**
     * Sidebar ID
     * @var string
     */
    protected $sidebar_id = '';

    /**
     * Post type of term
     * @var string
     */
    protected $post_type = '';

    /**
     * Hide Title
     * @var bool
     */
    protected $hide_title = false;

    /**
     * Hide Desc
     * @var bool
     */
    protected $hide_desc = false;

    /**
     * Show description
     * @var bool
     */
    protected $desciption = false;

    /**
     * Style display widget
     * @var string
     */
    protected $layout = 'icon';

    /**
     * Direction format
     * - horizontal : ==
     * - vertical : ||
     * @var string
     */
    protected $direction = 'vertical';

    /**
     * Shape
     *
     * @var string
     */
    protected $shape = 'circle';

     /**
     * Size
     *
     * @var string
     */
    protected $size = 'medium';

    /**
     * Include only these term_ids
     * @var array
     */
    protected $term_ids = [];

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
     * Default limit
     * @var int
     */
    protected $limit = 6;

    /**
     * Options
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
            $this->id_base = 'listar_api_taxonomy';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Api Taxonomy', 'listar');
        }

        $this->post_type = Listar::$post_type . '_category';

        $this->before_widget = '';
        $this->after_widget = '';

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
     * Get data for mobile
     * @param array $instance
     * @param Category_Model|Location_Model $model
     * @return array
     */
    public static function json($instance = [], $model = NULL)
    {
        $term_ids = isset($instance['term_ids']) ? $instance['term_ids'] : [];

        $args = [
            'taxonomy' => $instance['post_type'],
            'include' => $term_ids,
        ];
        
        $terms = get_terms($args);
        $data = [];
        
        list($module, $type) = explode('_', $instance['post_type']);
        if(!empty($terms)) {
            foreach($terms as $term) {
                // Fix special character
                $term->name = htmlspecialchars_decode($term->name);
                if(!is_null($model)) {
                    $model::assign_metadata($term);
                }
                $data[] = $term;
            }
        }
        
        $shape = isset($instance['shape']) ? $instance['shape'] : 'circle';
        $layout = isset($instance['layout']) ? $instance['layout'] : 'icon';

        return [
            'title' => isset($instance['title']) ? $instance['title'] : '',
            'description' => isset($instance['desc']) ? $instance['desc'] : '',
            'hide_title' => isset($instance['hide_title']) && (int) $instance['hide_title'] === 1,
            'hide_desc' => isset($instance['hide_desc']) && (int) $instance['hide_desc'] === 1,
            'layout' => $layout.'-'.$shape,
            'direction' => isset($instance['direction']) ? $instance['direction'] : 'horizontal',
            'shape' => $shape,
            'type' => $type,
            'data' => $data
        ];
    }

    /**
     * Widget render html/content
     * @param array $args | Optional
     * @param array $instance | setting from UI
     */
    public function widget($args = [], $instance = [])
    {
        if(isset($args['id'])) {
            $this->sidebar_id = $args['id'];
        }
        $this->initialize($args);
        $this->initialize($instance);

        $args = [
            'taxonomy' => $this->post_type,
            'include' => $this->term_ids,
            'number' => $this->limit
        ];

        if(!empty($this->term_ids)) {
            unset($args['number']);
        }

        $terms = get_terms($args);

        echo wp_kses_post($this->before_widget);

        if (!empty($instance['title']) && !$this->hide_title) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
            if (isset($instance['desc']) && !empty($instance['desc']) && !$this->hide_desc) {
                echo '<p class="text-muted">' . $instance['desc'] . '</p>';
            }
        }

        echo '<div class="row row-cols-1 row-cols-xs-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-6 row-cols-xl-6 row-cols-xxl-6 g-4">';
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $meta_data = get_term_meta($term->term_id, '', TRUE);
                $meta_data = listar_convert_single_value($meta_data);
                $image = NULL;
                if(isset($meta_data['featured_image']) && $meta_data['featured_image']) {
                    $image = wp_get_attachment_image_src( $meta_data['featured_image'], 'medium');
                }
                ?>
                <div class="col">
                    <div class="d-flex align-items-center rounded listar-card-03">
                        <div class="p-2 flex-fill listar-card-03-content">
                            <?php if($image) { ?>
                                <a href="<?php echo get_term_link($term);?>" title="<?php echo esc_attr($term->name);?>">
                                    <div class="fill-image listar-img-hover" style="background-image: url('<?php echo esc_url($image[0]);?>');">
                                    </div>
                                </a>
                            <?php } ?>
                            <div class="w-100 pt-2">
                                <h6 class="fw-bold mb-0">
                                    <a href="<?php echo get_term_link($term);?>" title="<?php echo esc_attr($term->name);?>">
                                        <?php echo esc_attr($term->name);?>
                                    </a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        echo '</div>';

        echo wp_kses_post($this->after_widget);
    }

    /**
     * Widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {   
        $direction_options   = [
            'horizontal' => __('Horizontal', 'listar'), 
            'grid' => __('Grid', 'listar'), 
            'list' => __('List', 'listar'), 
        ];
        $direction = isset($instance['direction']) ? esc_attr($instance['direction']) : $this->direction;
        $layout_options   = [
            'icon' => __('Icon', 'listar'), 
            'image' => __('Image', 'listar'), 
        ];
        $layout = isset($instance['layout']) ? esc_attr($instance['layout']) : $this->layout;        
        $shape_options   = [
            'circle' => __('Circle', 'listar'), 
            'round' => __('Round', 'listar'), 
            'square' => __('Square', 'listar'), 
            'landscape' => __('Landscape', 'listar'), 
            'portrait' => __('Portrait', 'listar'), 
        ];
        $shape = isset($instance['shape']) ? esc_attr($instance['shape']) : $this->shape;
        $size_options = [
            'small' => __('Small', 'listar'), 
            'medium' => __('Medium', 'listar'), 
            'large' => __('Large', 'listar'), 
        ];
        $size = isset($instance['size']) ? esc_attr($instance['size']) : $this->size;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
            <input type="text"
                   class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><b><?php _e('Description', 'listar'); ?></b></label>
            <textarea class="widefat"
                      id="<?php echo esc_attr($this->get_field_id('desc')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('desc')); ?>"
                      rows="3"> <?php echo isset($instance['desc']) ? esc_attr($instance['desc']) : ''; ?></textarea>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hide_title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_title')); ?>"
                   value="1" <?php echo isset($instance['hide_title']) && absint($instance['hide_title']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hide_title')); ?>"><?php _e('Hide Title', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hide_desc')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_desc')); ?>"
                   value="1" <?php echo isset($instance['hide_desc']) && absint($instance['hide_desc']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hide_desc')); ?>"><?php _e('Hide Description', 'listar'); ?></label>
        </p>
        <p><!-- Layout -->
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>"><b><?php _e('Mobile Layout', 'listar'); ?></b></label><br/>
            <?php foreach($layout_options as $value => $label) { ?>
            <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>" style="padding-right: 20px">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('layout')); ?>"
                       value="<?php echo esc_attr($value); ?>"
                    <?php echo esc_attr($layout === $value) ? 'checked="checked"' : ''; ?>
                >
                <?php echo esc_attr($label); ?>
            </label>
            <?php } ?>
        </p>        
        <p><!-- Shape -->
            <label for="<?php echo esc_attr($this->get_field_id('shape')); ?>"><b><?php _e('Shape', 'listar'); ?></b></label><br/>
            <?php foreach($shape_options as $value => $label) { ?>
            <label for="<?php echo esc_attr($this->get_field_id('shape')); ?>" style="padding-right: 20px">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('shape')); ?>"
                       value="<?php echo esc_attr($value); ?>"
                    <?php echo esc_attr($shape === $value) ? 'checked="checked"' : ''; ?>
                >
                <?php echo esc_attr($label); ?>
            </label>
            <?php } ?>
        </p>
        <p><!-- Direction -->
            <label for="<?php echo esc_attr($this->get_field_id('direction')); ?>"><b><?php _e('Direction', 'listar'); ?></b></label><br/>
            <?php foreach($direction_options as $value => $label) { ?>
            <label for="<?php echo esc_attr($this->get_field_id('direction')); ?>" style="padding-right: 20px">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('direction')); ?>"
                       value="<?php echo esc_attr($value); ?>"
                    <?php echo esc_attr($direction === $value) ? 'checked="checked"' : ''; ?>
                >
                <?php echo esc_attr($label); ?>
            </label>
            <?php } ?>
        </p>
        <div><!-- Term ids -->
            <label for="<?php echo esc_attr($this->get_field_id('term_ids')); ?>">
                <b><?php _e('Select item will be displayed', 'listar'); ?></b>
            </label>
            <div style="overflow: scroll; padding-left: 10px; height: 200px">
            <?php
            $terms = get_terms([
                'taxonomy'   => $this->post_type,
                'hide_empty' => false,
            ]);
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    if (isset($instance['term_ids']) && is_array($instance['term_ids']) && in_array($term->term_id, $instance['term_ids'])) {
                        $checked = ' checked="true"';
                    } else {
                        $checked = null;
                    }
                    if($term->parent === 0) {
                        ?>
                            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('term_ids')) . '_' . $term->term_id; ?>"
                                   name="<?php echo esc_attr($this->get_field_name('term_ids')); ?>[]"
                                   value="<?php echo esc_attr($term->term_id); ?>" <?php echo esc_attr($checked); ?> />
                            <label for="<?php echo esc_attr($this->get_field_id('term_ids')) . '_' . $term->term_id; ?>">
                                <?php echo esc_html($term->name); ?>
                            </label>
                            <br />
                        <?php
                        foreach( $terms as $sub_term) {
                            if($sub_term->parent === $term->term_id) {
                                if (isset($instance['term_ids']) && is_array($instance['term_ids']) && in_array($sub_term->term_id, $instance['term_ids'])) {
                                    $checked = ' checked="true"';
                                } else {
                                    $checked = null;
                                }
                                ?>
                                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('term_ids')) . '_' . $sub_term->term_id; ?>"
                                       name="<?php echo esc_attr($this->get_field_name('term_ids')); ?>[]"
                                       value="<?php echo esc_attr($sub_term->term_id); ?>" <?php echo esc_attr($checked); ?> />
                                <label for="<?php echo esc_attr($this->get_field_id('term_ids')) . '_' . $sub_term->term_id; ?>">
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
        $instance = [
            'title' => (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '',
            'desc' => (!empty($new_instance['desc'])) ? trim(sanitize_textarea_field($new_instance['desc'])) : '',
            'hide_title' => (!empty($new_instance['hide_title'])) ? $new_instance['hide_title'] : 0,
            'hide_desc' => (!empty($new_instance['hide_desc'])) ? $new_instance['hide_desc'] : 0,
            'layout' => (!empty($new_instance['layout'])) ? $new_instance['layout'] : $this->layout,
            'direction' => (!empty($new_instance['direction'])) ? $new_instance['direction'] : $this->direction,
            'shape' => (!empty($new_instance['shape'])) ? $new_instance['shape'] : $this->shape,
            'size' => (!empty($new_instance['size'])) ? $new_instance['size'] : $this->size,
            'term_ids' => (!empty($new_instance['term_ids'])) ? $new_instance['term_ids'] : [],
        ];

        return $instance;
    }
}

?>
