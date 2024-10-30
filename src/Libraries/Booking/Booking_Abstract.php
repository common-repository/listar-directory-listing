<?php
namespace ListarWP\Plugin\Libraries\Booking;
use Exception;

abstract class Booking_Abstract {
    /**
     * @var int
     */
    public $price = 0;
    /**
     * Total person
     * @var int
     */
    public $person = 0;

    /**
     * total adult
     * @var int
     */
    public $adult = 0;

    /**
     * Total children
     * @var int
     */
    public $children = 0;

    /**
     * end date
     * @var string
     */
    public $start_date = '';

    /**
     * start date
     * @var string
     */
    public $end_date = '';

    /**
     * start time
     * @var string
     */
    public $start_time = '';

    /**
     * end time
     * @var string
     */
    public $end_time = '';

    /**
     * table number
     * > booking type = table
     * @var null
     */
    public $table_num = 0;

    /**
     * Memo
     * @var string
     */
    public $memo = '';

    /**
     * for validate max day
     * @var int
     */
    protected $max_day = 0;

    /**
     * for validate max duration
     * @var int
     */
    protected $max_duration = 0;

    /**
     * for validate max person
     * @var int
     */
    protected $max_person = 0;

    /**
     * ID of user submit the listing
     * @var int
     */
    protected $author = 0;

    /**
     * validate
     * @return bool
     * @throws Exception
     */
    abstract public function validate();

    /**
     * Return total item base on booking type
     * @return int
     */
    abstract public function qty();

    /**
     * Get total price after calculation
     * @return int
     */
    abstract public function total();

    /**
     * Get options
     * @return array
     */
    abstract public function options();

    /**
     * Select option by booking style
     * @return array
     */
    abstract public function select_options();

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
     * Get total person
     * - Default is person
     * - Otherwise get adult + children as total person
     * @return int
     */
    public function total_person()
    {
        if(!$this->person && ($this->adult > 0 || $this->children > 0)) {
            return (int) $this->adult + (int)$this->children;
        }

        return $this->person;
    }

    /**
     * Total days diff between
     * @return int
     * @throws Exception
     */
    public function total_day()
    {
        if(!$this->start_date) {
            throw new Exception(__('Please select start date'));
        }

        if(!preg_match("/\d{4}\-\d{2}-\d{2}/", $this->start_date)) {
            throw new Exception(__('Start date is undefined format'));
        }

        if($this->end_date && !preg_match("/\d{4}\-\d{2}-\d{2}/", $this->end_date)) {
            throw new Exception(__('End date is undefined format'));
        }

        if($this->start_date && (!$this->end_date || $this->end_date === $this->start_date)) {
            return 1;
        } else {
            return listar_days_between($this->start_date, $this->end_date);
        }
    }

    /**
     * Total hours diff between
     * @return int
     * @throws Exception
     */
    public function total_hour()
    {
        if(!$this->start_time) {
            throw new Exception(__('Please select start time'));
        }

        if($this->start_time && (!$this->end_time || $this->start_time === $this->end_time)) {
            return 1;
        } else {
            return listar_hours_between($this->start_time, $this->end_time);
        }
    }

    /**
     * Get booking meta data insert
     * @return array
     */
    public function booking_meta_data()
    {
        return [
            '_person' => $this->total_person(),
            '_adult' => $this->adult,
            '_children' => $this->children,
            '_start_date' => $this->start_date,
            '_end_date' => $this->end_date,
            '_start_time' => $this->start_time,
            '_end_time' => $this->end_time,
            '_memo' => $this->memo,
            '_table_num' => $this->table_num,
        ];
    }

    /**
     * Validate max day
     * @throws Exception
     */
    public function validate_max_day()
    {
        if($this->total_day() > $this->max_day) {
            throw new Exception(__('Max booking days is '.$this->max_day. ' day(s)'));
        }
    }

    /**
     * Validate max duration
     * @throws Exception
     */
    public function validate_max_duration()
    {
        if($this->total_hour() > $this->max_duration) {
            throw new Exception(__('Max booking duration is '.$this->max_duration. ' hour(s)'));
        }
    }

    /**
     * validate max person
     * @throws Exception
     */
    public function validate_max_person()
    {
        if($this->total_person() > $this->max_person) {
            throw new Exception(__('Max person for booking is '.$this->max_person. ' persons(s)'));
        }
    }
}
