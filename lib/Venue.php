<?php
/**
 * All the funcitonality for the venue post type that is required on both the
 * admin and front end.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class Venue extends EventBase
{
    public static function init()
    {
        add_action(
            'init',
            array(__CLASS__, 'register_type')
        );
    }

    public static function register_type()
    {
        $labels = static::gen_type_labels(
            _x('Venue', 'pmg_venue', 'the-event'),
            _x('Venues', 'pmg_venue', 'the-event')
        );
        $labels['all_items'] = _x('Venues', 'pmg_venue', 'the-event');

        $slug = self::opt('venue_slug', 'venue');
        if(!$slug)
            $slug = 'venue';

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => false,
            'show_in_menu'          => 'edit.php?post_type=' . static::EVENT_TYPE,
            'capability_type'       => 'page',
            'supports'              => array('title', 'editor', 'thumbnail'),
            'rewrite'               => array(
                'slug'       => $slug,
                'with_front' => false
            )
        );

        $args = apply_filters('pmg_venue_type_args', $args);

        register_post_type(static::VENUE_TYPE, $args);
    }
} // end class Venue
