<?php
/**
 * Listar Nav Menu Walker to create HTML for list of pages
 */

namespace ListarWP\Plugin\Walkers;
use Walker_Nav_Menu;
use WP_Post;
use stdClass;

class Nav_Menu extends Walker_Nav_Menu {
    public function __construct() {
        add_filter('nav_menu_css_class' , [$this, 'filter_css_class'] , 10 , 4);
        add_filter('nav_menu_link_attributes' , [$this, 'filter_link_attributes'] , 10 , 4);
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
        if($depth === 0) {
            $classes[] = 'nav-item mb-2';
        }

        // Add has child > add more dropdown props
        if($args->walker->has_children) {
            $classes[] = 'dropdown';
        }

        return $classes;
    }

    /**
     * Add customize class for a element
     * > Ex: <a class="{class}" />
     * @param array $atts
     * @param WP_Post $menu_item
     * @param stdClass $args
     * @param int $depth
     * @return array
     * @since 1.0.0
     */
    public function filter_link_attributes($atts, $menu_item, $args, $depth) {
        if($depth === 0) {
            $atts['class'] = 'nav-link';
        } else {
            $atts['class'] = 'dropdown-item';
        }

        // Add has child > add more dropdown props
        if($args->walker->has_children) {
            $atts['href'] = '#';
            $atts['data-bs-toggle'] = 'dropdown';
            $atts['id'] = 'menu-link-item-'.$menu_item->ID;

            $args->link_before = '<span>';
            if($depth == 0) {
                $args->link_after = ' </span><i class="fas fa-chevron-down fa-xs"></i>';
            } else {
                $args->link_after = ' </span><i class="fas fa-chevron-right fa-xs"></i>';
            }
        } else {
            $args->link_after = '';
        }

        return $atts;
    }

    /**
     * Starts the list before the elements are added.
     *
     * @see Walker::start_lvl()
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );

        // Default class.
        $classes = array( 'sub-menu', 'dropdown-menu' );

        /**
         * Filters the CSS class(es) applied to a menu list element.
         *
         * @since 4.8.0
         *
         * @param string[] $classes Array of the CSS classes that are applied to the menu `<ul>` element.
         * @param stdClass $args    An object of `wp_nav_menu()` arguments.
         * @param int      $depth   Depth of menu item. Used for padding.
         */
        $class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
        $output .= "{$n}{$indent}<ul $class_names aria-labelledby=\"menu-link-item-{$args->menu->term_id}\">{$n}";
    }
}
