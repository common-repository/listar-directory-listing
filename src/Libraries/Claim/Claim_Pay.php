<?php
namespace ListarWP\Plugin\Libraries\Claim;
use ListarWP\Plugin\Libraries\Claim\Claim_Abstract;
use Exception;


class Claim_Pay extends Claim_Abstract {


    /**
     * Change as approved and waiting for pay to complete
     */
    public function accept()
    {
        wp_update_post([
            'ID' => $this->claim->get_id(),
            'post_status' => 'publish' 
        ]);
    }

    /**
     * Complete process payment
     *
     * @return void
     */
    public function complete()
    {
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
}
