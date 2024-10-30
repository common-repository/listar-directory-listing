<?php
/**
 * Widget for side menu
 */
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Widgets\Common\Menu;

class LocationMenu extends Menu
{
    public function __construct()
    {
        $this->id_base = 'listar_location_menu';
        $this->name = __('[Listar] Location Menu', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->post_type = Listar::$post_type.'_location';
    }
}

