<?php
/*
Plugin Name: Listar Directory Listing
Plugin URI: https://passionui.com
Description: Listar WordPress Plugin provides solution for help you organize your listings.
Version: 1.0.35
Author: Paul
Author URI: https://www.facebook.com/passionui/
License: GPL2
Text Domain: listar
Domain Path: /languages
*/

/**
 * Copyright (c) 2020 Paul (email: paul.passionui@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */
if ( !defined( 'LISTAR_PATH' ) ) define( 'LISTAR_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
if ( !defined( 'LISTAR_PLUGIN_URL' ) ) define( 'LISTAR_PLUGIN_URL', untrailingslashit(plugins_url('/', __FILE__)) );
if (!defined('ABSPATH')) exit;

include_once (__DIR__.'/vendor/autoload.php');
include_once LISTAR_PATH.'/includes/autoloader.php';
include_once LISTAR_PATH. '/includes/functions.php';

function listar_plugin() {
    /**
     * Initialize the Listar Plugin
     */
    $plugin = ListarWP\Plugin\Listar::get_instance();
    $plugin->run();
}

listar_plugin();
