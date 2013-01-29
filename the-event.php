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

require_once PMG_TE_PATH . 'lib/functions.php';
require_once PMG_TE_PATH . 'tt/events.php';

spl_autoload_register('pmg_events_autoload');

Meta::set_prefix(EventBase::PREFIX);

Event::init();
Artist::init();
Venue::init();
P2PIntegration::init();

if (is_admin()) {
    EventAdmin::init();
    ArtistAdmin::init();
    VenueAdmin::init();
    AdminOptions::init();
}
