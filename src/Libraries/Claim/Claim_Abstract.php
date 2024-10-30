<?php
namespace ListarWP\Plugin\Libraries\Claim;
use ListarWP\Plugin\Models\Claim_Model;

abstract class Claim_Abstract {

    /**
     * Claim model data
     * @var Claim_Model
     */
    protected $claim;

    /**
     * Method of charge
     * @var string
     */
    public $method_charge = '';

    /**
     * Claim price
     *
     * @var integer
     */
    public $price = 0;

    /**
     * Claim id
     *
     * @var integer
     */
    public $claim_id = 0;

    /**
     * Admin accept
     */
    abstract public function accept();
    abstract public function complete();

    public function __construct()
    {

    }

    /**
     * setup props
     * @param array $data
     * @return self
     */
    public function initialize($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return void
     */
    public function set_price($price = 0)
    {
        $this->price = 0;
    }

    /**
     * Set Claim item
     *
     * @param Claim_Model $claim
     * @return void
     */
    public function set_claim($claim)
    {
        $this->claim = $claim;
    }

     /**
     * Set method
     *
     * @param string $method
     * @return void
     */
    public function set_method_charge($method = '')
    {
        $this->method_charge = $method;
    }
}
