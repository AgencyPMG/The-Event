<?php
/*
Plugin Name: The Event
Plugin URI: https://github.com/AgencyPMG/The-Event
Description: An events calendar without the front end.
Version: 0.1
Text Domain: the-event
Domain Path: /lang
Author: Christopher Davis
Author URI: http://pmg.co/people/chris
License: GPL2

    Copyright 2012 Performance Media Group <seo@pmg.co>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

define('PMG_TE_PATH', plugin_dir_path(__FILE__));
define('PMG_EVENT_EP', 262144);

spl_autoload_register(__NAMESPACE__ . '\\autoloader');
/**
 * Autoloader function.
 *
 * @since   0.1
 * @param   string $cls The class name.
 * @return  null
 */
function autoloader($cls)
{
    $cls = ltrim($cls, '\\');
    if(strpos($cls, __NAMESPACE__) !== 0)
        return; // not this namespace

    $cls = str_replace(__NAMESPACE__, '', $cls);

    $path = PMG_TE_PATH . 'lib' .
        str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';

    require_once($path);
}

Event::init();
Artist::init();
Venue::init();
