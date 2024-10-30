<?php
namespace ListarWP\Plugin\Libraries\Payment;
use ListarWP\Plugin\Libraries\Customer;
use ListarWP\Plugin\Libraries\Cart;

class Payment_Default extends Payment_Abstract {

    public function __construct(Customer $customer = NULL, Cart $cart = NULL)
    {
        parent::__construct($customer, $cart);

        $this->title = __('Check Payments');
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function complete()
    {
        // TODO: Implement complete() method.
    }

    /**
     * @inheritDoc
     */
    public function cancel()
    {
        // TODO: Implement cancel() method.
    }

    /**
     * @inheritDoc
     */
    public function get_title()
    {
        return $this->title;
    }
}
