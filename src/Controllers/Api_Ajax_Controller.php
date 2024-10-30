<?php
namespace ListarWP\Plugin\Controllers;

class Api_Ajax_Controller {

    public function __construct() {
        add_action('wp_ajax_get_stage_list', [$this, 'get_stage_list']);
    }

    /**
     * Get list stages
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function get_stage_list() {
        $parent_id = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : NULL;

        if ( is_wp_error( $parent_id ) ) {
            wp_send_json_error();
        }

        $data = get_terms( 'listar_location', [
            'parent'        => $parent_id,
            'hide_empty'    => 0
        ]);

        wp_send_json_success($data);
    }
}
