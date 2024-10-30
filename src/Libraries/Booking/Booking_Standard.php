<?php
namespace ListarWP\Plugin\Libraries\Booking;
use ListarWP\Plugin\Models\Setting_Model;
use Exception;

class Booking_Standard extends Booking_Abstract {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Total guest
     * @return int
     * @throws
     */
    public function qty()
    {
        return $this->total_day();
    }

    /**
     * Total price
     * = days * price
     * @return float|int
     * @throws Exception
     */
    public function total()
    {
        return $this->qty()*$this->price;
    }

    /**
     * Get options
     * @return array
     * @throws Exception
     */
    public function options()
    {
        return [
            'guest' => $this->total_person(),
            'adult' => $this->adult,
            'children' => $this->children,
            'days' => $this->total_day(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function select_options()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->max_person = absint(Setting_Model::get_option('booking_standard_max_person'));
        $this->validate_max_person();
    }
}
