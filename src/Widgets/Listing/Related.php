<?php
namespace ListarWP\Plugin\Widgets\Listing;

class Related extends Data
{
    protected $taxonomy = 'listar_category';

    /**
     * Options
     * @param array $args
     */
    public function __construct($args = [])
    {
        $this->display_number = 8;
        $this->id_base = 'listar_listing_related';
        $this->name = __('[Listar] Related Listing', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->initialize($args);
    }

    /**
     * Widget form handler
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        parent::form($instance);

        $taxonomy_use = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : '';

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('taxonomy')); ?>"><b><?php _e('Similar', 'listar'); ?></b></label>
            <select id="<?php echo esc_attr($this->get_field_id('taxonomy')); ?>" name="<?php echo esc_attr($this->get_field_name('taxonomy')); ?>">
                <option <?php echo esc_attr($taxonomy_use == '') ? "selected='selected'" : '';?> value=""><?php _e('Select taxonomy', 'listar');?></option>
                <option <?php echo esc_attr($taxonomy_use == 'listar_category') ? "selected='selected'" : '';?> value="listar_category"><?php _e('Category', 'listar');?></option>
                <option <?php echo esc_attr($taxonomy_use == 'listar_location') ? "selected='selected'" : '';?> value="listar_location"><?php _e('Location', 'listar');?></option>
                <option <?php echo esc_attr($taxonomy_use == 'listar_feature') ? "selected='selected'" : '';?> value="listar_feature"><?php _e('Feature', 'listar-wp');?></option>
            </select>
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
        $instance = parent::update($new_instance, $old_instance);
        $instance['taxonomy'] = (!empty($new_instance['taxonomy'])) ? trim(sanitize_textarea_field($new_instance['taxonomy'])) : '';

        return $instance;
    }
}
