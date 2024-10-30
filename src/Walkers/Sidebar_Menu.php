<?php
namespace ListarWP\Plugin\Walkers;
use Walker_Category;

class Sidebar_Menu extends Walker_Category {
    /**
     * Count
     * @var bool
     */
    protected $count = false;

    /**
     * Settings
     * @param array $args
     */
    public function __construct($args = []) {
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
     * Starts the list before the elements are added.
     *
     * @param string $output
     * @param int $depth
     * @param array $args
     */
    function start_lvl(&$output, $depth=1, $args=array()) {
        $indent  = str_repeat( "\t", $depth );
        $output .= "$indent<li class='list-group-item border border-0 px-0 pt-0'>
            <ul class='list-group list-group-flush ps-4'>\n";
    }

    /**
     * Start the element output.
     *
     * @param string $output
     * @param \WP_Term $item
     * @param int $depth
     * @param array $args
     * @param int $current_object_id
     */
    function start_el(&$output, $item, $depth=0, $args=array(),$current_object_id = 0) {
        $count = '';
        $class_name = isset($args['current_category']) && $args['current_category'] == $item->term_id ? 'active' : '';

        if($this->count && $item->count > 0) {
            $count = "<span class='badge text-bg-light rounded-pill'>{$item->count}</span>";
        }

        $output .= "<li id='nav-menu-item-{$item->term_id}' 
            class='list-group-item d-flex justify-content-between align-items-center border border-0 px-0'>".
            "<a title='".$item->name."' class='".$class_name."' href='".get_term_link($item)."'>".esc_html($item->name)."</a>".$count;
    }
}
