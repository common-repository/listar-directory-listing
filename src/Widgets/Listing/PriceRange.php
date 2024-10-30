<?php
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Models\Setting_Model;
use WP_Widget;


class PriceRange extends WP_Widget
{
    /**
     * Price
     * @var string
     */
    protected $unit_price = '';

    /**
     * Symbol
     * @var string
     */
    protected $unit_symbol = '';

    /**
     * Price max
     * @var int
     */
    protected $price_max = 500;

    /**
     * Price min
     * @var int
     */
    protected $price_min = 0;

    public function __construct()
    {
        parent::__construct(
            'listar_price_range',  // Base ID
            __('[Listar] Price Range', 'listar')   // Name
        );

        $this->unit_price = get_option('listar_unit_price');
        $this->unit_symbol = get_option('listar_unit_symbol');
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

        if (!empty($instance['price_max'])) {
            $this->price_max = absint($instance['price_max']);
        }

        if (!empty($instance['price_min'])) {
            $this->price_min = absint($instance['price_min']);
        }

        $price_default = floor($this->price_max - $this->price_min)/2;
        $price_get = absint(listar_get_request_parameter('price', 0));
        if($price_get > 0) {
            $price_default = $price_get;
        }

        $price_min_get = listar_get_request_parameter('price_min', '');
        $price_max_get = listar_get_request_parameter('price_max', '');

        $type = isset($instance['type']) && $instance['type'] ? $instance['type'] : 'range';
        ?>

        <?php if( $type == 'range') { ?>
        <div class="d-block">
            <div class="text-end">
                <span class="text-muted">from </span>
                <span class="fw-bold" id="priceRangeLabel">
                    <?php echo Setting_Model::currency_format($price_default);?>
                </span>
            </div>
            <input type="range"
                   class="form-range"
                   name="price"
                   id="priceRange"
                   aria-label="<?php _e('Price range', 'listar-wp');?>"
                   onchange="listarSidebar.onPriceRangeChange(this);"
                   data-unit="<?php echo esc_attr($this->unit_symbol);?>"
                   min="<?php echo esc_attr($this->price_min);?>"
                   max="<?php echo esc_attr($this->price_max);?>"
                   value="<?php echo esc_attr($price_default);?>"
            />
            <div class="d-flex justify-content-between">
                <div class="text-muted"><?php echo Setting_Model::currency_format($this->price_min);?></div>
                <div class="text-muted"><?php echo Setting_Model::currency_format($this->price_max);?></div>
            </div>
            <div class="d-grid gap-2">
                <button id="priceRangeConfirmBtn"
                    class="btn btn-sm btn-outline-primary mt-2"
                >
                    <?php _e('Apply', 'listar');?>
                </button>
            </div>
        </div>
        <?php } else { ?>
        <!-- Range Input -->
        <div class="row g-2 mt-3">
            <div class="form-group col-md-6">
                <label><?php _e('Min', 'listar');?></label>
                <input class="form-control"
                       id="priceMin"
                       name="price_min"
                       placeholder="<?php echo Setting_Model::currency_format($this->price_min);?>"
                       value="<?php echo esc_attr($price_min_get);?>"
                       type="number"
                >
            </div>
            <div class="form-group text-right col-md-6">
                <label><?php _e('Max', 'listar');?></label>
                <input class="form-control"
                       id="priceMax"
                       name="price_max"
                       aria-label="<?php _e('Price range', 'listar-wp');?>"
                       placeholder="<?php echo Setting_Model::currency_format($this->price_max);?>"
                       value="<?php echo esc_attr($price_max_get);?>"
                       type="number"
                >
            </div>
        </div>
        <div class="d-grid me-2">
            <button id="priceInputConfirmBtn"
                    onclick="listarSidebar.onPriceInputConfirmBtn(this);"
                    class="btn btn-sm btn-outline-primary mt-2"
            ><?php _e('Apply', 'listar');?></button>
        </div>
        <?php }

        echo wp_kses_post($this->args['after_widget']);
    }

    public function form($instance)
    {
        $type = isset($instance['type']) && $instance['type'] != '' ? $instance['type'] : 'range';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo isset($instance['title']) ? esc_attr($instance['title']) : ''; ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('type')); ?>"
                   style="padding-right: 20px"
            >
                <input type="radio"
                        name="<?php echo esc_attr($this->get_field_name('type')); ?>"
                        value="range"
                    <?php echo esc_attr($type === 'range') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('Range', 'listar'); ?>
            </label>
            <label for="<?php echo esc_attr($this->get_field_id('type')); ?>">
                <input type="radio"
                       name="<?php echo esc_attr($this->get_field_name('type')); ?>"
                        value="input"
                    <?php echo esc_attr($type === 'input') ? 'checked="checked"' : ''; ?>
                >
                <?php _e('Input', 'listar'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('price_min')); ?>"><b><?php _e('Price Min', 'listar'); ?></b></label>
            <input type="text"
                   class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('price_min')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('price_min')); ?>"
                   value="<?php echo isset($instance['title']) ? esc_attr($instance['price_min']) : ''; ?>"
            />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('price_max')); ?>"><b><?php _e('Price Max', 'listar'); ?></b></label>
            <input type="text"
                   class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('price_max')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('price_max')); ?>"
                   value="<?php echo isset($instance['title']) ? esc_attr($instance['price_max']) : ''; ?>"
            />
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
        $instance = [
            'title' => (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '',
            'price_min' => (!empty($new_instance['price_min'])) ? trim(sanitize_textarea_field($new_instance['price_min'])) : '',
            'price_max' => (!empty($new_instance['price_max'])) ? trim(sanitize_textarea_field($new_instance['price_max'])) : '',
            'type' => (!empty($new_instance['type'])) ? trim(sanitize_textarea_field($new_instance['type'])) : '',
        ];

        return $instance;
    }
}
?>
