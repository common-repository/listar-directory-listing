<?php
namespace ListarWP\Plugin\Libraries;

class Product {

    /**
     * Product ID
     * @var string
     */
    public $id = '';

    /**
     * product name
     * @var string
     */
    public $name = '';

    /**
     * product price
     * @var int
     */
    public $price = 0;

    /**
     * ID of user make the listing
     * @var int
     */
    public $author = 0;

    /**
     * Author email (owner submitted listing)
     *
     * @var string
     */
    public $author_email = '';

    /**
     * Set data
     * Product constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->initialize($data);
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
     * Get product id
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Get product price
     * @return int
     */
    public function get_price()
    {
        return $this->price;
    }

    /**
     * Get product name
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @since 1.0.21
     * @return int
     */
    public function get_author()
    {
        return $this->author;
    }

    /**
     * @since 1.0.30
     * @return string
     */
    public function get_author_email()
    {
        return $this->author_email;
    }
}
