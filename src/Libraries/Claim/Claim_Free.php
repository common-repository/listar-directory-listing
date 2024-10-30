<?php
namespace ListarWP\Plugin\Libraries\Claim;
use ListarWP\Plugin\Libraries\Claim\Claim_Abstract;
use Exception;


class Claim_Free extends Claim_Abstract {

    /**
     * Auto change status as completed if the method of charge is free
     */
    public function accept()
    {   
        /**
         * Claim
         * - Change status as completed without process payment
         * - Set meta data
         */
        // Change status
        wp_update_post([
            'ID' => $this->claim->get_id(),
            'post_status' => 'completed',
        ]);

        // Update meta data
        update_post_meta($this->claim->get_id(), 'claim_price', 0);    
        update_post_meta($this->claim->get_id(), 'claim_unit_price', '');    

        /**
         * Listing
         * - Reset the author
         * - Mark as claimed
         */
        // Set the author who make the claim listing
        wp_update_post([
            'ID' => $this->claim->get_listing_id(),
            'post_author' => $this->claim->get_author()
        ]);        

        // Mark as claimed
        update_post_meta($this->claim->get_listing_id(), 'claim_use', 1);
    }

    /**
     * Complete process payment
     *
     * @return void
     */
    public function complete()
    {
        // Process not thing now
    }
}
