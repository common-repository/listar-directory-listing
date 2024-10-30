<?php
namespace ListarWP\Plugin\Libraries;

use ListarWP\Plugin\Models\Category_Model;
use ListarWP\Plugin\Models\Location_Model;
use ListarWP\Plugin\Widgets\Api\Taxonomy;
use ListarWP\Plugin\Widgets\Api\Location;
use ListarWP\Plugin\Widgets\Api\Category;
use ListarWP\Plugin\Widgets\Api\Banner;
use ListarWP\Plugin\Widgets\Api\Post;
use ListarWP\Plugin\Widgets\Listing\Data;
use Locale;

/**
 * Support return json data for mobile via mobile widget
 */
class Api_Widget {

    /**
     * Sidebar ID
     * @var string
     */
    static $mobile_sidebar = 'listar-mobile-home-sidebar';

    /**
     * Term id query
     * @var string 
     */
    static $option = NULL;

    /**
     * Term id set 
     * @var string 
     */
    static $taxonomy = NULL;
    
    public static function set_option($option = 0) {
        self::$option = $option;
    }

    /**
     * Taxonomy type set
     *
     * @param string $taxonomy
     * @return void
     */
    public static function set_taxonomy($taxonomy = '') {
        self::$taxonomy = $taxonomy;
    }

    /**
     * Data widget
     *
     * @return array
     */
    public static function data_widgets() {
        $data_widgets = [];
        $sidebars_widgets = get_option('sidebars_widgets');
        $mobile_sidebars = isset($sidebars_widgets[self::$mobile_sidebar]) ? $sidebars_widgets[self::$mobile_sidebar] : [];
        $index_widgets = [];

        foreach($mobile_sidebars as $sidebar) {
            $instance = NULL;
            $pattern = '/([a-zA-Z0-9_\s]+)\-([0-9]+)/';
            // Ex: listar_data-6 > id = listar_data and index = 6
            preg_match($pattern, $sidebar, $matches);
            if(isset($matches[1]) && isset($matches[2])) {
                $sidebard_id = $matches[1];
                $widget_id = $matches[2];

                if(!isset($index_widgets[$sidebard_id])) {
                    $widgets = get_option('widget_'.$sidebard_id);
                    $index_widgets[$sidebard_id] = $widgets;
                } else {
                    $widgets = $index_widgets[$sidebard_id];
                }

                if(isset($widgets[$widget_id])) {
                    $instance = $widgets[$widget_id];
                }
               
                switch($sidebard_id) {
                    case 'listar_data':                        
                        if(!is_null(self::$option) && self::$option > 0) {
                            if(self::$taxonomy == 'location') {
                                $instance['location'] = self::$option;
                            } else if(self::$taxonomy == 'category') {
                                $instance['location'] = self::$option;    
                            }
                        }
                        $data_widgets[] = Data::json($instance);
                        break;
                    case 'listar_api_category':
                        $data_widgets[] = Category::json($instance, new Category_Model);
                        break;
                    case 'listar_api_location':
                        $data_widgets[] = Location::json($instance, new Location_Model);
                        break;
                    case 'listar_api_taxonomy':
                        $data_widgets[] = Taxonomy::json($instance);
                        break;
                    case 'listar_api_banner':
                        $data_widgets[] = Banner::json($instance);
                        break;
                    case 'listar_api_post':
                        $data_widgets[] = Post::json($instance);
                        break;
                }
            }
        }

        return $data_widgets;
    }
}
