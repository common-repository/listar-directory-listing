<?php
namespace ListarWP\Plugin\Widgets\Post;
use ListarWP\Plugin\Widgets\Common\Menu;

class Category extends Menu
{
    public function __construct()
    {
        $this->id_base = 'listar_post_category';
        $this->name = __('[Listar] Post Category', 'listar');

        parent::__construct([
            'id' => $this->id_base,
            'name' => $this->name,
        ]);

        $this->post_type = 'category';
    }
}

