<?php
namespace ListarWP\Plugin\Controllers;

use ListarWP\Plugin\Libraries\Convert\Listing;
use ListarWP\Plugin\Libraries\Convert\Taxonomy;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Place_Model;
use WP_Error;

class Admin_Import_Controller {

    static $page = 'import';

    public function __construct() {
        add_action('admin_menu', [$this, 'add']);
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
            __('Import', 'listar'), __('Import', 'listar'), 'manage_options', self::$page, [$this, 'form']);
    }

    /**
     * Load form UI
     *
     * @return void
     * @version 1.0.3
     */
    public function form() {
        $listar = Listar::get_instance();
        $action = isset($_REQUEST['action'])? sanitize_text_field($_REQUEST['action']) : NULL;
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'listing';

        $max_file_size = ini_get('upload_max_filesize');
        $status = [
            'publish' => __('Publish'),
            'draft' => __('Draft'),
            'pending' => __('Pending'),
            'private' => __('Private'),
        ];

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

        /**
         * Handle post form
         */        
        $post_error = '';
        $post_errors = [];
        $post_import = [];

        $post_status = isset($_REQUEST['status'])? sanitize_text_field($_REQUEST['status']) : NULL;
        $post_exist = isset($_REQUEST['exist'])? sanitize_text_field($_REQUEST['exist']) === 'true' : FALSE;
        $post_taxonomy = isset($_REQUEST['taxonomy']) ? sanitize_text_field($_REQUEST['taxonomy']) : 'listar_category';                                        

        if ($action === 'save') {
            if($_FILES['upload']['name']) {
                if(!$_FILES['upload']['error']) {
                    $tmp_file = $_FILES['upload']['tmp_name'];
                                    
                    if($active_tab == 'listing') {
                        Listing::$post_status = $post_status;
                        Listing::$post_exist = $post_exist;
                        Listing::import($tmp_file);
                        // Set final error
                        $post_errors = Listing::$errors;
                        $post_import = Listing::$result;
                    } else if($active_tab == 'taxonomy') {                                                    
                        Taxonomy::import($post_taxonomy, $tmp_file);
                        // Set final error
                        $post_errors = Taxonomy::$errors;
                        $post_import = Taxonomy::$result;
                    }                       
                }
            } else {
                $post_error = __('Please select file for upload', 'listar');
            }
        }

        include_once $listar->plugin_path() . '/views/convert/import.php';
    }
}
