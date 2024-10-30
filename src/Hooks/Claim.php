<?php
namespace ListarWP\Plugin\Hooks;
use ListarWP\Plugin\Models\Claim_Model;
use ListarWP\Plugin\Libraries\Notify;
use Exception;
use WP_Post;

/**
 * Define common hook support Admin & API
 */
class Claim {
    static $post_type;

    public function __construct()
    {
        self::$post_type = Claim_Model::post_type();
        
        /**
         * Transition Post Status
         * @refer: https://codex.wordpress.org/Post_Status_Transitions
         */

        // Admin approved
        add_action('pending_to_publish',  [$this, 'approve'], 10, 1 ); 
        
        // User cancelled or admin cancelled
        add_action('canceled_'.self::$post_type,  [$this, 'canceled'], 10, 2 );

        // User completed the final step (payment)
        add_action('completed_'.self::$post_type,  [$this, 'completed'], 10, 2 );

        /**
         * Customization Hook 
         */
        
        // Submit (Add listing)
        add_action('submit_listar_claim', [$this, 'submit'], 10, 2);

        // Request (Claim to request)
        add_action('request_listar_claim', [$this, 'request'], 10, 3);

        // Approve
        add_action('approve_listar_claim', [$this, 'approve'], 10, 1);
    }

    /**
     * Approve the request
     * 
     * @param WP_Post $claim
     * @param bool $update
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.31
     */
    public function approve($post)
    {
        try {
            if($post->post_type == self::$post_type) {
                $claim_model = new Claim_Model();
                $claim_item = $claim_model->item($post->ID);

                $claim = new \ListarWP\Plugin\Libraries\Claim();
                $claim->accept($claim_item);
            }
        } catch(Exception $e) {
            error_log('hook.claim.accept:'.$e->getMessage());
        }
    }

    /**
     * Cancelled
     * - User force cancel
     * - User cancel payment
     * - Admin change status 
     * 
     * @param int $ID Claim ID
     * @param WP_Post $post Claim data
     */
    public function canceled($ID, $post)
    {
        try {
            if($post->post_type == self::$post_type) {
                $claim_model = new Claim_Model();
                $claim_model->data = (array)$post;
                $claim_model->assign_meta_data($ID);

                $claim_data = $claim_model->get_item_view($ID);
                Notify::notify_claim_cancel($claim_data);
            }
        } catch(Exception $e) {
            error_log('hook.claim.canceled:'.$e->getMessage());
        }
    }

    /**
     * Completed
     * - User processed payment 
     * - Callback successfully 
     * - Free > Approved > Completed
     * 
     * @param int $ID Claim ID
     * @param WP_Post $post Claim data
     */
    public function completed($ID, $post)
    {
        try {
            if($post->post_type == self::$post_type) {
                $claim_model = new Claim_Model();
                $claim_model->data = (array)$post;
                $claim_model->assign_meta_data($ID);

                $claim_data = $claim_model->get_item_view($ID);
                Notify::notify_claim_complete($claim_data);
            }
        } catch(Exception $e) {
            error_log('hook.claim.canceled:'.$e->getMessage());
        }
    }

    /**
     * Submit
     * - User submit the listing
     * @param int $ID Claim insert ID
     * @param array $post Claim data
     */
    public function submit($ID, $post)
    {
        try {
            // Notify
            Notify::notify_claim_submit($post);
        } catch(Exception $e) {
            error_log('hook.claim.submit:'.$e->getMessage());
        }
    }

    /**
     * Request
     * - User submit the listing
     * @param int $listing_id Listing ID
     * @param int $claim_id Claim ID 
     * @param array $data Claim data
     */
    public function request($listing_id, $claim_id, $data)
    {
        try {
            // Notify
            Notify::notify_claim_request($data);
        } catch(Exception $e) {
            error_log('hook.claim.request:'.$e->getMessage());
        }
    }
}