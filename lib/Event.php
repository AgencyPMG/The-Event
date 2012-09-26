<?php
/**
 * Contains all the functionality for the event post type that needs to work on
 * both the admin and front end.
 *
 * @see             EventAdmin for admin specific function
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class Event extends EventBase
{
    public static function init()
    {
        add_action(
            'init',
            array(__CLASS__, 'register_type')
        );

        add_action(
            'init',
            array(__CLASS__, 'register_cat'),
            20
        );

        add_action(
            'init',
            array(__CLASS__, 'register_tag'),
            20
        );
    }

    public static function register_type()
    {
        $labels = static::gen_type_labels(
            __('Event', 'the-event'),
            __('Events', 'the-event')
        );
        $labels['menu_name'] = __('The Event', 'the-event');

        $slug = static::opt('event_slug', 'events');
        if(!$slug)
            $slug = 'events';

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => false,
            'menu_position'         => 400, // way low
            'capability_type'       => 'page',
            'supports'              => array(
                'title', 'editor', 'thumbnail', 'excerpt'),
            'taxonomies'            => array(static::EVENT_CAT, static::EVENT_TAG),
            'has_archive'           => true,
            'rewrite'               => array(
                'slug'       => $slug,
                'with_front' => false,
                'ep_mask'    => PMG_EVENT_EP
            )
        );

        $args = apply_filters('pmg_event_type_args', $args);

        register_post_type(self::EVENT_TYPE, $args);
    }

    public static function register_cat()
    {
        $labels = static::gen_tax_labels(
            _x('Category', 'pmg_event_cat', 'the-event'),
            _x('Categories', 'pm_event_cat', 'the-event')
        );
        $labels['menu_name'] = __('Event Categories', 'the-event');

        $slug = static::opt('cat_slug', 'event-category');
        if(!$slug)
            $slug = 'event-category';

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menues'    => false,
            'hierarchical'          => true,
            'rewrite'               => array(
                'slug'       => $slug,
                'with_front' => false
            )
        );

        $args = apply_filters('pmg_event_cat_args', $args);

        register_taxonomy(static::EVENT_CAT, static::EVENT_TYPE, $args);
    }

    public static function register_tag()
    {
        $labels = static::gen_tax_labels(
            _x('Tag', 'pmg_event_tag', 'the-event'),
            _x('Tags', 'pmg_event_tag', 'the-event')
        );
        $labels['menu_name'] = __('Event Tags', 'the-event');

        $slug = static::opt('tag_slug', 'event-tag');
        if(!$slug)
            $slug = 'event-tag';

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menues'    => false,
            'rewrite'               => array(
                'slug'       => $slug,
                'with_front' => false
            )
        );

        $args = apply_filters('pmg_event_tag_args', $args);

        register_taxonomy(static::EVENT_TAG, static::EVENT_TYPE, $args);
    }
} // end class Event
