<?php
namespace ListarWP\Plugin\Libraries\Booking;
use ListarWP\Plugin\Models\Setting_Model;
use Exception;

class Booking_Hourly extends Booking_Abstract {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Total hours
     * @return int|void
     * @throws Exception
     */
    public function qty()
    {
        return $this->total_hour();
    }

    /**
     * Total price
     * @return float|int
     * @throws Exception
     */
    public function total()
    {
        return $this->qty()*$this->price;
    }

    /**
     * Option
     * @return array
     * @throws Exception
     */
    public function options()
    {
        return [
            'guest' => $this->total_person(),
            'adult' => $this->adult,
            'children' => $this->children,
            'hours' => $this->total_hour()
        ];
    }

    /**
     * @inheritDoc
     */
    public function select_options()
    {
        $start_time = Setting_Model::get_option('booking_hourly_start_time');
        $end_time = Setting_Model::get_option('booking_hourly_end_time');
        $duration = Setting_Model::get_option('booking_hourly_duration');

        return listar_schedule_slot($start_time, $end_time, $duration);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->max_duration = absint(Setting_Model::get_option('booking_hourly_max_duration'));
        $this->validate_max_duration();

        $this->max_person = absint(Setting_Model::get_option('booking_hourly_max_person'));
        $this->validate_max_person();
    }
}
