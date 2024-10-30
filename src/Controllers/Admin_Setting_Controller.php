<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Setting_Model;

class Admin_Setting_Controller {

    static $page = 'settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'add']);

        // Loads admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
    }

    public function load_scripts() {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('listar-admin-js');
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
            __('Settings', 'listar'), __('Settings', 'listar'), 'manage_options', self::$page, [$this, 'form']);
    }

    /**
     * Load form UI
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     */
    public function form() {
        $listar = Listar::get_instance();
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'mobile';
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : NULL;
        $section = isset($_REQUEST['section']) ? sanitize_text_field($_REQUEST['section']) : NULL;
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : NULL;

        if ($page === self::$page && $action) {
            if ($action === 'save') {
                $post_options = Setting_Model::get_combine_options($active_tab);
                foreach ($post_options as $value) {
                    if (isset($_REQUEST[$value['id']])) {
                        // Auto-paragraphs for any WYSIWYG
                        switch ($value['type']) {
                            case 'wysiwyg':
                                $data = wpautop(sanitize_textarea_field($_REQUEST[$value['id']]));
                                break;
                            case 'sortable':
                                $data = json_encode(array_values($_REQUEST[$value['id']]));
                                break;
                            case 'checkbox':
                                $data = trim($_REQUEST[$value['id']]);
                                break;
                            default:
                                $data = trim(sanitize_textarea_field($_REQUEST[$value['id']]));
                                break;
                        }

                        update_option($value['id'], $data);
                    }
                }

                // Affect URL setting change
                flush_rewrite_rules();
            }
        }

        /**
         * These variable will be used in views
         * @var $tab_options : use for setting form
         * @var $tab_data : use for setting form
         */
        $tab_options    = Setting_Model::get_options();
        $tab_data       = Setting_Model::get_options($active_tab, $section);

        // First click active to section default
        if(!$section && !isset($tab_data['options']) && isset($tab_data['default_section'])) {
            $section = $tab_data['default_section'];
            $tab_data = $tab_data['sections'][$section];
        }

        include_once $listar->plugin_path() . '/views/setting/option.php';
    }
}
