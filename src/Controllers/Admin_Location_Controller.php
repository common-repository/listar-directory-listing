<?php
namespace ListarWP\Plugin\Controllers;
use ListarWP\Plugin\Listar;
use ListarWP\Plugin\Models\Location_Model;
use WP_Term;

class Admin_Location_Controller {

    public function __construct() {
        add_action( 'created_listar_location', [$this, 'save']);
        add_action( 'edited_listar_location', [$this, 'save']);

        add_action( 'listar_location_add_form_fields', [$this, 'form']);
        add_action( 'listar_location_edit_form_fields', [$this, 'form']);

        // Loads admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'load_scripts']);

        /**
         * Customize display list
         * @since 1.0.15
         */
        // manage_edit_{TAXONOMY}_columns
        add_filter( 'manage_edit-listar_location_columns', [$this, 'admin_columns_filter'] );
        // manage_{TAXONOMY}_custom_column
        add_action( 'manage_listar_location_custom_column', [$this, 'admin_columns_action'], 10, 3);
    }

    /**
     * Load scripts
     * - Css
     * - Javascript
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_scripts() {
        // Load modal upload file + media select
        wp_enqueue_media();

        // Fontawesome
        wp_enqueue_style('fontawesome');
        wp_enqueue_style('fontawesome-iconpicker-css');
        wp_enqueue_script('fontawesome-iconpicker-js');

        // Color picker
        wp_enqueue_script( 'wp-color-picker');
        wp_enqueue_style( 'wp-color-picker' );

        // Admin Scripts
        wp_enqueue_style('listar-admin-css');
        wp_enqueue_script('listar-admin-js');
    }

    /**
     * Render value & html
     *
     * @param WP_Term $term
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function form($term) {
        $listar = Listar::get_instance();
        Location_Model::assign_metadata($term);

        if(is_object($term) && $term->term_id) {
            include_once $listar->plugin_path() . '/views/metadata/location-edit.php';
        } else {
            include_once $listar->plugin_path() . '/views/metadata/location.php';
        }
    }

    /**
     * Saving form data
     *
     * @param $term_id
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function save($term_id) {
        // Set metadata by defined fields
        Location_Model::set_metadata(absint($term_id), $_POST);
    }

    /**
     * Column filter
     * @param $columns
     * @return array
     * @since 1.0.15
     */
    public function admin_columns_filter( $columns ) {
        $columns['image'] =__( 'Image');
        return $columns;
    }

    /**
     * Column action
     * @param string $content
     * @param string $columns
     * @param int $term_id
     * @since 1.0.15
     */
    public function admin_columns_action($content = '', $columns = '',$term_id = 0) {
        // Image column
        switch ($columns) {
            case 'image';
                $attachment_id = get_term_meta($term_id, 'featured_image', TRUE);
                $size = 'thumbnail';
                $image = wp_get_attachment_image_src( $attachment_id, $size);
                if(!empty($image) && isset($image[0])) {
                    echo '<img style="max-width:80px" src="' . $image[0] . '" />';
                }
                break;
        }
    }
}
