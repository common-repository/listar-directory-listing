<?php
namespace ListarWP\Plugin\Libraries\Claim;
use ListarWP\Plugin\Libraries\Claim\Claim_Abstract;
use Exception;


class Claim_Plan extends Claim_Abstract {


    /**
     * Change as approved and waiting for pay to complete
     */
    public function accept()
    {
       // Process not thing now
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
