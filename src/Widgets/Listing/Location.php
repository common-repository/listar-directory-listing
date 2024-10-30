<?php
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Widgets\Common\Grid;

class Location extends Grid
{
    /**
     * Options
     * @param array $args
     */
    public function __construct($args = [])
    {
        $this->id_base = 'listar_location';
        $this->name = __('[Listar] Location', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->post_type = Listar::$post_type.'_location';

        $this->initialize($args);
    }
}
