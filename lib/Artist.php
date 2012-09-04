<?php
/**
 * All the funcitonality for the artist post type that is required on both the
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

class Artist extends EventBase
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
            _x('Artist', 'pmg_artist', 'the-event'),
            _x('Artists', 'pmg_artist', 'the-event')
        );
        $labels['all_items'] = _x('Artists', 'pmg_artist', 'the-event');

        $slug = self::opt('artist_slug', 'artist');
        if(!$slug)
            $slug = 'artist';

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

        $args = apply_filters('pmg_artist_type_args', $args);

        register_post_type(static::ARTIST_TYPE, $args);
    }
} // end class Artist
