<?php
namespace ListarWP\Plugin\Widgets\Api;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Category_Model;
use ListarWP\Plugin\Models\Location_Model;
use ListarWP\Plugin\Widgets\Api\Taxonomy;

class Category extends Taxonomy
{
    /**
     * Options
     * @param array $args
     */
    public function __construct($args = [])
    {
        $this->id_base = 'listar_api_category';
        $this->name = __('[Listar] Api Category', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->post_type = Listar::$post_type.'_category';
        
        $this->initialize($args);
    }

    /**
     * Load json API
     *
     * @param array $instance
     * @param Category_Model|Location_Model $instance
     * @return array
     */
    public static function json($instance = [], $model = NULL) 
    {
        $instance['post_type'] = Listar::$post_type.'_category';
        return parent::json($instance, $model);   
    }
}
