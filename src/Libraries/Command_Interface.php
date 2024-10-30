<?php
namespace ListarWP\Plugin\Libraries;

interface Command_Interface {
    /**
     * Get command name 
     * @return string
     */
    public static function command_name();
}
