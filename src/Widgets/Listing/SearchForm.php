<?php
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use WP_Widget;

class SearchForm extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'listar_search_form',  // Base ID
            __('[Listar] Listing Search Form', 'listar')  // Name
        );
    }

    public $args = array(
        'before_title'  => '',
        'after_title'   => '',
        'before_widget' => '',
        'after_widget'  => ''
    );

    /**
     * Widget render html/content
     * @param array $args
     * @param array $instance
     */
    public function widget($args = [], $instance = [])
    {
        if(isset($instance['simple']) && (int) $instance['simple'] === 1) {
            echo '<aside id="search" class="widget widget_search">';
            get_template_part('template-parts/listar/searchform', 'sidebar');
            echo '</aside>';
        } else {
            $category = listar_get_request_parameter('category');
            $image = isset( $instance['image'] ) ? $instance['image'] : '';
            $taxonomy_use = isset($instance['taxonomy_use']) && $instance['taxonomy_use'] != '' ? $instance['taxonomy_use'] : '';
            $taxonomy = [];

            if($taxonomy_use) {
                $taxonomy = get_terms(Listar::$post_type . '_'.$taxonomy_use, [
                    'parent' => 0,
                    'hide_empty' => 0
                ]);
                $class_keyword = 'col-sm-12 col-md-12 col-lg-5 col-xl-5 col-xxl-5 li-item';
            } else {
                $class_keyword = 'col-sm-12 col-md-12 col-lg-9 col-xl-9 col-xxl-9 li-item';
            }

            echo wp_kses_post($this->args['before_widget']);

            ?>
            <div class="listar-advance-search container listar-py-60 pt-0">
                <div class="row align-items-center mt-md-2 position-relative listar-header">
                    <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6">
                        <h1 class="display-4 fw-bold animate__animated animate__slideInLeft"><?php echo isset($instance['title']) ? esc_attr($instance['title']) : '';?></h1>
                        <p class="fs-5 listar-py-10 text-muted animate__animated animate__slideInLeft animate__fast"><?php echo isset($instance['desc']) ? esc_attr($instance['desc']) : '';?></p>
                        <div class="listar-py-20">
                            <div class="shadow bg-body li-form listar-py-20" style="z-index: 2;">
                                <form class="row align-items-center w-100 justify-content-center"
                                      role="search"
                                      method="get"
                                      action="<?php echo listar_get_listing_url(); ?>"
                                >
                                    <?php if($taxonomy_use) { ?>
                                    <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4 li-item">
                                        <label class="fw-bold">
                                            <?php echo isset($instance['taxonomy_label']) ? esc_attr($instance['taxonomy_label']) : '';?>
                                        </label>
                                        <div class="dropdown">
                                            <button
                                                    class="btn btn-link text-muted dropdown-toggle d-flex justify-content-between w-100 align-items-center"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                            >
                                                <span><?php echo isset($instance['taxonomy_placeholder']) ? esc_attr($instance['taxonomy_placeholder']) : '';?></span>
                                                <i class="fas fa-chevron-down fa-xs"></i>
                                            </button>
                                            <?php if(!empty($taxonomy)) { ?>
                                            <ul class="dropdown-menu w-100 shadow" id="listar-searchform-location">
                                                <?php
                                                    foreach ($taxonomy as $term) {
                                                        $class_active = $term->term_id == $category ? 'active ' : '';
                                                        echo '<li><a class="dropdown-item '.$class_active.'" href="'.get_term_link($term).'" title="'.esc_attr($term->name).'">'.esc_attr($term->name).'</a></li>';
                                                    }
                                                ?>
                                            </ul>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="<?php echo esc_attr($class_keyword);?>">
                                        <label class="fw-bold" for="listar-searchform-keyword">
                                            <?php echo isset($instance['keyword_label']) ? esc_attr($instance['keyword_label']) : '';?>
                                        </label>
                                        <input type="text"
                                               name="s"
                                               class="form-control"
                                               id="listar-searchform-keyword"
                                               placeholder="<?php echo isset($instance['keyword_placeholder']) ? esc_attr($instance['keyword_placeholder']) : '';?>">
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 col-xl-3 col-xxl-3 li-btn-search">
                                        <button type="submit" class="btn btn-primary li-btn-primary">
                                            <?php echo isset($instance['button_txt']) ? esc_attr($instance['button_txt']) : '';?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6 li-col-mobile">
                        <?php if($image) { ?>
                            <img class="float-start w-100 li-border-radius animate__animated animate__fadeIn animate__delay-1s" src="<?php echo esc_attr($image); ?>"
                                 alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" />
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php

            echo wp_kses_post($this->args['after_widget']);
        }
    }

    /**
     * Setting form
     * @param array $instance
     * @return string|void
     */
    public function form($instance)
    {
        $title = isset( $instance['title'] ) ? $instance['title'] : 'Find Your Destination';
        $desc = isset( $instance['desc'] ) ? $instance['desc'] : '';
        $image = isset( $instance['image'] ) ? $instance['image'] : get_template_directory_uri()."/assets/img/header-image.jpg";
        $button_txt = isset( $instance['button_txt'] ) ? $instance['button_txt'] : __( 'Search', 'listar' );
        $keyword_label = isset( $instance['keyword_label'] ) ? $instance['keyword_label'] : __( 'Keyword', 'listar' );
        $keyword_placeholder = isset( $instance['keyword_placeholder'] ) ? $instance['keyword_placeholder'] : __( 'Ex: food, service, hotel', 'listar' );
        $taxonomy_use = isset( $instance['taxonomy_use'] ) ? $instance['taxonomy_use'] : '';
        $taxonomy_label = isset( $instance['taxonomy_label'] ) ? $instance['taxonomy_label'] : __( 'Taxonomy', 'listar' );
        $taxonomy_placeholder = isset( $instance['taxonomy_placeholder'] ) ? $instance['taxonomy_placeholder'] : __( 'Select Option', 'listar' );

        ?>
        <p>
            <input type="checkbox"
                   id="<?php echo esc_attr($this->get_field_id('simple')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('simple')); ?>"
                   value="1" <?php echo isset($instance['simple']) && absint($instance['simple']) === 1 ? ' checked="checked"' : null; ?> />
            <label for="<?php echo esc_attr($this->get_field_id('simple')); ?>"><?php _e('Simple Search (Only show search field)', 'listar'); ?></label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><b><?php _e('Title', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo esc_attr($title) ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><b><?php _e('Description', 'listar'); ?></b></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('desc')); ?>"
                      name="<?php echo esc_attr($this->get_field_name('desc')); ?>" rows="5"> <?php echo esc_attr($desc); ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('image')); ?>"><b><?php _e('Image', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('image')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('image')); ?>"
                   value="<?php echo esc_attr($image); ?>" />
            <small class="description"><?php _e('Please enter the URL of image. Ex: http://domain.com/image.jpg (1280 × 960 px)', 'listar');?></small>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('button_txt')); ?>"><b><?php _e('Search Button', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('button_txt')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('button_txt')); ?>"
                   value="<?php echo esc_attr($button_txt) ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('keyword_label')); ?>"><b><?php _e('Keyword Label', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('keyword_label')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('keyword_label')); ?>"
                   value="<?php echo esc_attr($keyword_label) ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('keyword_placeholder')); ?>"><b><?php _e('Keyword Placeholder', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('keyword_placeholder')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('keyword_placeholder')); ?>"
                   value="<?php echo esc_attr($keyword_placeholder) ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('taxonomy_use')); ?>"><b><?php _e('Taxonomy', 'listar'); ?></b></label><br />
            <select id="<?php echo esc_attr($this->get_field_id('taxonomy_use')); ?>" name="<?php echo esc_attr($this->get_field_name('taxonomy_use')); ?>">
                <option <?php echo esc_attr($taxonomy_use == '') ? "selected='selected'" : '';?> value=""><?php _e('Hidden', 'listar');?></option>
                <option <?php echo esc_attr($taxonomy_use == 'category') ? "selected='selected'" : '';?> value="category"><?php _e('Category', 'listar');?></option>
                <option <?php echo esc_attr($taxonomy_use == 'location') ? "selected='selected'" : '';?> value="location"><?php _e('Location', 'listar');?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('taxonomy_label')); ?>"><b><?php _e('Taxonomy Label', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('taxonomy_label')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('taxonomy_label')); ?>"
                   value="<?php echo esc_attr($taxonomy_label) ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('taxonomy_placeholder')); ?>"><b><?php _e('Taxonomy Placeholder', 'listar'); ?></b></label>
            <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id('taxonomy_placeholder')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('taxonomy_placeholder')); ?>"
                   value="<?php echo esc_attr($taxonomy_placeholder) ?>" />
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
        $instance['simple'] = (!empty($new_instance['simple'])) ? trim(sanitize_textarea_field($new_instance['simple'])) : '';
        $instance['title'] = (!empty($new_instance['title'])) ? trim(sanitize_textarea_field($new_instance['title'])) : '';
        $instance['desc'] = (!empty($new_instance['desc'])) ? trim(sanitize_textarea_field($new_instance['desc'])) : '';
        $instance['image'] = (!empty($new_instance['image'])) ? trim(sanitize_textarea_field($new_instance['image'])) : '';
        $instance['button_txt'] = (!empty($new_instance['button_txt'])) ? trim(sanitize_textarea_field($new_instance['button_txt'])) : '';
        $instance['keyword_label'] = (!empty($new_instance['keyword_label'])) ? trim(sanitize_textarea_field($new_instance['keyword_label'])) : '';
        $instance['keyword_placeholder'] = (!empty($new_instance['keyword_placeholder'])) ? trim(sanitize_textarea_field($new_instance['keyword_placeholder'])) : '';
        $instance['taxonomy_use'] = (!empty($new_instance['taxonomy_use'])) ? trim(sanitize_textarea_field($new_instance['taxonomy_use'])) : '';
        $instance['taxonomy_label'] = (!empty($new_instance['taxonomy_label'])) ? trim(sanitize_textarea_field($new_instance['taxonomy_label'])) : '';
        $instance['taxonomy_placeholder'] = (!empty($new_instance['taxonomy_placeholder'])) ? trim(sanitize_textarea_field($new_instance['taxonomy_placeholder'])) : '';
        return $instance;
    }
}
?>
