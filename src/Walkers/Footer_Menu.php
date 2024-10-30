<?php
namespace ListarWP\Plugin\Walkers;
use Walker_Nav_Menu;
use WP_Post;
use stdClass;

class Footer_Menu extends Walker_Nav_Menu {
    public function __construct() {
        add_filter('nav_menu_css_class' , [$this, 'filter_css_class'] , 10 , 4);
        add_filter('nav_menu_link_attributes' , [$this, 'nav_menu_link_attributes'] , 10 , 4);
    }

    /**
     * Add customize class for li element
     * > Ex: <li class="{class}" />
     * @param string[] $classes
     * @param WP_Post $item
     * @param stdClass $args
     * @param int $depth
     * @return string[]
     * @since 1.0.0
     */
    public function filter_css_class($classes, $item, $args, $depth) {
        $classes[] = 'mb-2';

        return $classes;
    }

    /**
     * Add customize class for li element
     * @param array $atts
     * @param WP_Post $item
     * @param stdClass $args
     * @param int $depth
     * @return array
     * @since 1.0.0
     */
    public function nav_menu_link_attributes($atts, $item, $args, $depth) {
        $atts['class'] = 'text-white text-decoration-none';
        return $atts;
    }
}
