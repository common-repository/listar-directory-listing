<?php
/**
 * Home menu widget
 * - Circle
 * - Square
 * - Icon
 */
namespace ListarWP\Plugin\Widgets\Common;
use WP_Widget;
use ListarWP\Plugin\Listar;

class Grid extends WP_Widget
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
     * Count
     * @var bool
     */
    protected $count = true;

    /**
     * Show empty category/taxonomy
     * @var bool
     */
    protected $hide_empty  = false;

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
     * Check use background color
     * @var bool
     */
    protected $use_background = false;

    /**
     * Show description
     * @var bool
     */
    protected $desciption = false;

    /**
     * Style display widget
     * @var string
     */
    protected $style = 'style1';

    /**
     * Include only these categories
     * @var array
     */
    protected $categories = [];

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
            $this->id_base = 'listar_grid';
        }

        if(!$this->name) {
            $this->name = __('[Listar] Grid', 'listar');
        }

        $this->post_type = Listar::$post_type . '_category';

        $this->before_widget = '<div class="listar-py-60 container">';
        $this->after_widget = '</div>';

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

        switch ($this->style) {
            case 'style2':
                $this->square($instance);
                break;
            case 'style3':
                $this->icon($instance);
                break;
            default:
                $this->circle($instance);
                break;
        }
    }

    /**
     * Widget form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        $style_options = [
            'style1' => __('Style 1', 'listar'), // Circle
            'style2' => __('Style 2', 'listar'), // Square
            'style3' => __('Style 3', 'listar'), // Icon + Sub Menu
        ]
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
                   id="<?php echo esc_attr($this->get_field_id('count')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('count')); ?>"
                   value="1" <?php echo isset($instance['count']) && absint($instance['count']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('count')); ?>"><?php _e('Show counts', 'listar'); ?></label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('hide_empty')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('hide_empty')); ?>"
                   value="1" <?php echo isset($instance['hide_empty']) && absint($instance['hide_empty']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('hide_empty')); ?>"><?php echo __('Hide empty count', 'listar'); ?></label>
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
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('style')); ?>"><b><?php _e('Style Display', 'listar'); ?></b></label>
            <select id="<?php echo esc_attr($this->get_field_id('style')); ?>" name="<?php echo esc_attr($this->get_field_name('style')); ?>">
                <?php foreach($style_options as $style => $label) { ?>
                    <option <?php echo isset($instance['style']) && esc_attr($instance['style']) == $style ? "selected='selected'" : '';?>
                            value="<?php echo esc_attr($style);?>"><?php echo esc_attr($label);?></option>
                <?php } ?>
            </select>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('use_background')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('use_background')); ?>"
                   value="1" <?php echo isset($instance['use_background']) && absint($instance['use_background']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('use_background')); ?>"><?php _e('Use Background Color', 'listar'); ?></label>
        </p>
        <div>
            
            <label for="<?php echo esc_attr($this->get_field_id('categories')); ?>">
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
                    if (isset($instance['categories']) && is_array($instance['categories']) && in_array($term->term_id, $instance['categories'])) {
                        $checked = ' checked="true"';
                    } else {
                        $checked = null;
                    }
                    if($term->parent === 0) {
                        ?>
                            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $term->term_id; ?>"
                                   name="<?php echo esc_attr($this->get_field_name('categories')); ?>[]"
                                   value="<?php echo esc_attr($term->term_id); ?>" <?php echo esc_attr($checked); ?> />
                            <label for="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $term->term_id; ?>">
                                <?php echo esc_html($term->name); ?>
                            </label>
                            <br />
                        <?php
                        foreach( $terms as $sub_term) {
                            if($sub_term->parent === $term->term_id) {
                                if (isset($instance['categories']) && is_array($instance['categories']) && in_array($sub_term->term_id, $instance['categories'])) {
                                    $checked = ' checked="true"';
                                } else {
                                    $checked = null;
                                }
                                ?>
                                <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $sub_term->term_id; ?>"
                                       name="<?php echo esc_attr($this->get_field_name('categories')); ?>[]"
                                       value="<?php echo esc_attr($sub_term->term_id); ?>" <?php echo esc_attr($checked); ?> />
                                <label for="<?php echo esc_attr($this->get_field_id('categories')) . '_' . $sub_term->term_id; ?>">
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
            'style' => (!empty($new_instance['style'])) ? $new_instance['style'] : 'style1',
            'count' => (!empty($new_instance['count'])) ? $new_instance['count'] : 0,
            'hide_empty' => (!empty($new_instance['hide_empty'])) ? $new_instance['hide_empty'] : 0,
            'hide_title' => (!empty($new_instance['hide_title'])) ? $new_instance['hide_title'] : 0,
            'hide_desc' => (!empty($new_instance['hide_desc'])) ? $new_instance['hide_desc'] : 0,
            'use_background' => (!empty($new_instance['use_background'])) ? $new_instance['use_background'] : 0,
            'categories' => (!empty($new_instance['categories'])) ? $new_instance['categories'] : [],
        ];

        return $instance;
    }

    /**
     * Widget render html/content
     * @param array $instance
     */
    protected function icon($instance) {
        $terms = $this->_get_terms();

        if($this->use_background) {
            echo '<div class="container-fluid listar-bg-light">';
        }

        echo wp_kses_post($this->before_widget);

        if (!empty($instance['title']) && !$this->hide_title) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
            if (isset($instance['desc']) && !empty($instance['desc']) && !$this->hide_desc) {
                echo '<p class="text-muted">' . $instance['desc'] . '</p>';
            }
        }

        echo '<div class="row row-cols-1 row-cols-xs-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-4 g-4">';

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $meta_data = get_term_meta($term->term_id, '', TRUE);
                $meta_data = listar_convert_single_value($meta_data);
                if($term->parent === 0) {
                    ?>
                    <div class="col listar-component-category">
                        <div class="d-flex shadow rounded p-2">
                            <div class="p-2">
                                <div class="listar-component-category-icon"
                                     style="background-color: <?php echo esc_attr($meta_data['color']);?>; opacity: 0.75;"
                                >
                                    <i class="<?php echo esc_attr($meta_data['icon']);?> text-white"></i>
                                </div>
                            </div>
                            <div class="p-2 flex-fill">
                                <p class="fw-bold mb-1">
                                    <a href="<?php echo get_term_link($term);?>">
                                        <?php echo esc_attr($term->name);?>
                                    </a>
                                </p>
                                <ul class="list-group">
                                    <?php foreach( $terms as $sub_term) {
                                        if($sub_term->parent === $term->term_id) {
                                            ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center text-muted">
                                                <a href="<?php echo get_term_link($sub_term);?>">
                                                    <?php echo esc_attr($sub_term->name);?>
                                                </a>
                                                <?php if($this->count && $sub_term->count > 0) { ?>
                                                    <span class="badge text-bg-light rounded-pill">
                                                        <?php echo esc_attr($sub_term->count);?>
                                                    </span>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        echo '</div>';

        if($this->use_background) {
            echo '</div>';
        }

        echo wp_kses_post($this->after_widget);
    }

    /**
     * Widget render html/content as circle
     * @param array $instance
     */
    protected function circle($instance) {
        $terms = $this->_get_terms();

        if($this->use_background) {
            echo '<div class="container-fluid listar-bg-light">';
        }

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
                                <?php if($term->count > 0 && $this->count) { ?>
                                    <small class="text-muted mb-0"><?php echo esc_attr($term->count).' '.__('Listings', 'listar');?></small>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        echo '</div>';

        if($this->use_background) {
            echo '</div>';
        }

        echo wp_kses_post($this->after_widget);
    }

    /**
     * Widget render html/content
     * @param array $instance
     */
    protected function square($instance) {
        $terms = $this->_get_terms();

        if($this->use_background) {
            echo '<div class="container-fluid listar-bg-light">';
        }

        echo wp_kses_post($this->before_widget);

        if (!empty($instance['title']) && !$this->hide_title) {
            echo wp_kses_post($this->before_title) . apply_filters('widget_title', $instance['title']) . wp_kses_post($this->after_title);
            if (isset($instance['desc']) && !empty($instance['desc']) && !$this->hide_desc) {
                echo '<p class="text-muted">' . $instance['desc'] . '</p>';
            }
        }

        echo '<div class="li-card-thumb"><div class="row row-cols-1 row-cols-xs-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-4 g-4">';
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $meta_data = get_term_meta($term->term_id, '', TRUE);
                $meta_data = listar_convert_single_value($meta_data);
                $image = NULL;
                if(isset($meta_data['featured_image']) && $meta_data['featured_image']) {
                    $image = wp_get_attachment_image_src( $meta_data['featured_image'], 'medium_large');
                }
                ?>
                <div class="col listar-component-category">
                    <a href="<?php echo get_term_link($term);?>">
                        <div class="card border-0 position-relative">
                            <div class="fill-image-rectangle listar-img-hover" style="background-image: url('<?php echo esc_url($image[0]);?>');">
                            </div>
                            <div class="position-absolute bottom-0 start-0 m-3">
                                <h5 class="listar-card-title text-white">
                                    <?php echo esc_html($term->name);?>
                                </h5>
                                <?php if($term->count > 0) { ?>
                                    <p class="card-text text-white"><?php echo esc_attr($term->count).' '.__('Listings', 'listar');?> </p>
                                <?php } ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
        }

        echo '</div></div>';

        if($this->use_background) {
            echo '</div>';
        }

        echo wp_kses_post($this->after_widget);
    }

    /**
     * Get list category
     * @return int[]|string|string[]|\WP_Error|\WP_Term[]
     */
    private function _get_terms()
    {
        $args = [
            'taxonomy' => $this->post_type,
            'include' => $this->categories,
            'hide_empty' => $this->hide_empty,
            'number' => $this->limit
        ];

        if(!empty($this->categories)) {
            unset($args['number']);
        }

        return get_terms($args);
    }
}

?>
