<?php
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Widgets\Common\Grid;

class Category extends Grid
{
    /**
     * Options
     * @param array $args
     */
    public function __construct($args = [])
    {
        $this->id_base = 'listar_category';
        $this->name = __('[Listar] Category', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->post_type = Listar::$post_type.'_category';

        $this->initialize($args);
    }
}
