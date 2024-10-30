<?php
namespace ListarWP\Plugin\Libraries\Booking;
use ListarWP\Plugin\Models\Setting_Model;

class Booking_Table extends Booking_Abstract {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Total guest
     * @return int|void
     */
    public function qty()
    {
        return $this->total_person();
    }

    /**
     * Total price
     * @return float|int
     */
    public function total()
    {
        return $this->qty()*$this->price;
    }

    /**
     * Option
     * @return array
     */
    public function options()
    {
        return [
            'guest' => $this->total_person(),
            'adult' => $this->adult,
            'children' => $this->children,
            'table_num' => $this->table_num
        ];
    }

    /**
     * @inheritDoc
     */
    public function select_options()
    {
        $num = absint(Setting_Model::get_option('booking_total_table'));
        $result = [];
        for($i=1; $i<=$num; $i++) {
            $result[] = [
                'title' => $i,
                'id' => $i,
                'use' => rand(0, 1) === 1 ? true : false
            ];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->max_person = absint(Setting_Model::get_option('booking_table_max_person'));
        $this->validate_max_person();
    }
}
