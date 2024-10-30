<?php
namespace ListarWP\Plugin\Commands;

use ListarWP\Plugin\Libraries\Command_Interface;
use ListarWP\Plugin\Models\Job_Model;
use Exception;

class Job_Command implements Command_Interface {

    protected $environment;

    /**
     * Wordpress CLI command root 
     *
     * @var string
     */
    protected $wp_cli_bin = '/usr/local/bin/wp';

    /**
     * Errors
     */
    static $errors = [];

    /**
     * Warnings
     */
    static $warning = [];

    /**
     * Command name
     * @return string
     */
    public static function command_name()
    {
        return 'listar-job';    
    }

    /**
     * Construct 
     */
    public function __construct( ) 
    {
        $this->environment = wp_get_environment_type();
        if(defined('WP_CLI_BIN')) {
            $this->wp_cli_bin = WP_CLI_BIN;
        }        
    }
    
    /**
     * Run validate
     */
    public function dummy($args,  $assoc_args) 
    {  
        for($i=1; $i<=10; $i++) {
            Job_Model::insert([
                'type' => 'cmd',
                'task' => "{$this->wp_cli_bin} listar-notify mobile --id=1",
                'payload' => json_encode(['foo' => 'bar']),
            ]);
        }
    }

     /**
     * Run validate
     */
    public function run($args,  $assoc_args) 
    {       
        $args = wp_parse_args($assoc_args, ['action' => '']);
        $root_path = getcwd();
        $job = Job_Model::get_jobs_for_run();
        
        if($job) {

            if(WP_DEBUG === TRUE) {
                echo gmdate('Y-m-d H:i').": Processing job ($job->id)".PHP_EOL;
            }

            try {
                // task: start
                $start = microtime(true);
                $runtime = null;

                // task: update status for run
                Job_Model::update_status($job->id, [
                    'status' => Job_Model::STATUS_RUNNING
                ]);
                
                $payload = json_decode($job->payload, true);
                $payload = is_array($payload) ? $payload : [];

                // task: run
                switch($job->type) {
                    case 'cmd':
                        $response = shell_exec("cd {$root_path}; {$this->wp_cli_bin} {$job->task} > /dev/null &");
                        break;
                    case 'curl':
                        // CURL handler
                        break;   
                    default:
                        throw new Exception('Undefined type of job');     
                }                
                
                // task: end
                $runtime = microtime(true) - $start;                
                $status = Job_Model::STATUS_DONE;                
            } catch (Exception $e) {
                // task: error
                $runtime = microtime(true) - $start;                
                $status = Job_Model::STATUS_FAILED;
                $response = $e->getMessage();
            }            

            $runtime = $runtime === null ?  microtime(true) - $start : $runtime;
            $usage = memory_get_peak_usage()/ 1024 / 1024;

            // task: update status
            Job_Model::update_status($job->id, [
                'status' => $status,
                'run_time' => $runtime,
                'run_usage' => $usage,
                'response' => is_array($response) ? json_encode($response) : $response,
                'updated_on' => gmdate("Y-m-d H:i:s")
            ]);
        }

        sleep(1);
    }
}