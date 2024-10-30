<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Libraries\Convert\Listing;
use ListarWP\Plugin\Libraries\Convert\Taxonomy;
use ListarWP\Plugin\Listar;

class Admin_Export_Controller {

    static $page = 'export';

    /**
     * Mimes for validation
     * Image only
     * @var array
     */
    static $mimes = [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'tiff|tif' => 'image/tiff'
    ];

    /**
     * Error handler
     * @var array
     */
    static $errors = [];

    /**
     * Import status
     * @var array
     */
    static $result = [
        'total' => 0, // total rows
        'success' => 0, // insert successfully
        'error' => 0, // insert error
    ];

    /**
     * Term data
     * @var array
     */
    protected $term_index = [
        'tag' => [],
        'feature' => [],
        'category' => [],
        'location' => []
    ];

    public function __construct() {
        add_action('admin_menu', [$this, 'add']);
        add_action('admin_init', [$this, 'export_handler'] );
    }

    /**
     * Create Setting Menu
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function add() {
        $listar = Listar::get_instance();
        add_submenu_page('edit.php?post_type='.$listar::$post_type,
            __('Export', 'listar'), __('Export', 'listar'), 'manage_options', self::$page, [$this, 'form']);
    }

    /**
     * Export CSV handler
     */
    public function export_handler()
    {       
        if(isset($_GET['listar_export_taxonomy_csv'])) {
            $post_taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : 'listar_category';
            $term_name = sanitize_text_field($post_taxonomy);
            Taxonomy::export($term_name);
        } else if(isset($_GET['listar_export_listing_csv'])) {
            Listing::export();
        }
    }

    /**
     * Load form UI
     *
     * @return void
     * @version 1.0.3
     */
    public function form() {
        $listar = Listar::get_instance();
        $action = isset($_GET['action'])? sanitize_text_field($_GET['action']) : NULL;
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'listing';

        $tab_options = [
            'listing' => __('Listing', 'listar'),
            'taxonomy' => __('Taxonomy', 'listar'),
        ];

        $taxonomies = [
            'listar_category' => __('Categories', 'listar'),
            'listar_feature' => __('Features', 'listar'),
            'listar_location' => __('Locations', 'listar'),
            'post_tag' => __('Tags', 'listar'),
        ];

        $post_taxonomy = isset($_REQUEST['taxonomy']) ? sanitize_text_field($_REQUEST['taxonomy']) : 'listar_category';

        include_once $listar->plugin_path() . '/views/convert/export.php';
    }
}
