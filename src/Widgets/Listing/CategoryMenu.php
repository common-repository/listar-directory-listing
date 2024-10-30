<?php
/**
 * Widget for side menu
 */
namespace ListarWP\Plugin\Widgets\Listing;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Widgets\Common\Menu;

class CategoryMenu extends Menu
{
    public function __construct()
    {
        $this->id_base = 'listar_category_menu';
        $this->name = __('[Listar] Category Menu', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->post_type = Listar::$post_type.'_category';
    }
}

