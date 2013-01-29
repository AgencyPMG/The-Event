<?php
/**
 * Un-namespaced functions.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

!defined('ABSPATH') && exit;

function pmg_events_autoload($cls)
{
    $cls = ltrim($cls, '\\');
    if (strpos($cls, 'PMG\\TheEvent') !== 0) {
        return false; // not this namespace
    }

    $cls = str_replace('PMG\\TheEvent', '', $cls);

    $path = PMG_TE_PATH . 'lib' .
        str_replace('\\', DIRECTORY_SEPARATOR, $cls) . '.php';

    require_once $path;
}
