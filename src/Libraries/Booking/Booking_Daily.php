<?php
namespace ListarWP\Plugin\Libraries\Booking;
use ListarWP\Plugin\Models\Setting_Model;
use Exception;

class Booking_Daily extends Booking_Abstract {

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
        return $this->total_day();
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
            'days' => $this->total_day()
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
        if(Setting_Model::get_option('booking_daily_start_time_require') && !$this->start_time) {
            throw new Exception(__('Start time is required'));
        }

        if(Setting_Model::get_option('booking_daily_end_date_require') && !$this->end_date) {
            throw new Exception(__('End date is required'));
        }

        if(Setting_Model::get_option('booking_daily_end_time_require') && !$this->end_time) {
            throw new Exception(__('End time is required'));
        }

        $this->max_day = absint(Setting_Model::get_option('booking_daily_max_day'));
        $this->validate_max_day();

        $this->max_person = absint(Setting_Model::get_option('booking_daily_max_person'));
        $this->validate_max_person();
    }
}
