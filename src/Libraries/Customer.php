<?php
namespace ListarWP\Plugin\Libraries;

/**
 * Class Customer
 * @author paul.passionui@gmaill.com
 * @version 1.0.11
 */
class Customer {

    /**
     * Customer ID
     * @var string|integer
     */
    protected $id = '';

    /**
     * First name
     * @var string
     */
    public $first_name = '';

    /**
     * Last name
     * @var string
     */
    public $last_name = '';

    /**
     * Phone
     * @var string
     */
    public $phone = '';

    /**
     * Email
     * @var string
     */
    public $email = '';

    /**
     * Company
     * @var string
     */
    public $company = '';

    /**
     * Address
     * @var string
     */
    public $address = '';

    /**
     * Phone
     * @var string
     */
    public $country = '';

    /**
     * Phone
     * @var string
     */
    public $city = '';

    /**
     * Customer constructor.
     * @param int $id
     */
    public function __construct($id = 0)
    {
        $this->id = $id;
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
     * Get customer id
     * @return int|string
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Get billing address
     * @return string
     */
    public function billing_address_index()
    {
        $index = ['first_name', 'last_name', 'company', 'address', 'country', 'email', 'phone'];
        $result = '';
        foreach ($index as $item) {
            if(!empty($this->{$item})) {
                $result .= $this->{$item}.' ';
            }
        }

        return trim($result);
    }
}
