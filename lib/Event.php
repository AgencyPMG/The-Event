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

        add_action(
            'pre_get_posts',
            array(__CLASS__, 'set_order')
        );

        add_filter(
            'posts_where',
            array(__CLASS__, 'remove_old'),
            10,
            2
        );

        add_filter('wpseo_json_ld_output', array(__CLASS__, 'event_json_ld'), 10, 2);
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

    public static function set_order($q)
    {
        if(is_admin() || !$q->is_main_query())
            return;

        if(
            !is_post_type_archive(static::EVENT_TYPE) &&
            !is_tax(array(static::EVENT_CAT, static::EVENT_TAG))
        ) return;

        $q->set('orderby', 'post_date post_title');
        $q->set('order', 'ASC');
    }

    public static function remove_old($where, $q)
    {
        global $wpdb;

        if (is_admin() || !$q->is_main_query()) {
            return $where;
        }

        if(
            is_post_type_archive(static::EVENT_TYPE) ||
            is_tax(array(static::EVENT_CAT, static::EVENT_TAG))
        ) {
            $where .= $wpdb->prepare(
                " AND DATE({$wpdb->posts}.post_modified) >= %s",
                date('Y-m-d')
            );
        } elseif (is_search()) {
            $where .= $wpdb->prepare(
                " AND {$wpdb->posts}.ID NOT IN (
                    SELECT ID FROM {$wpdb->posts} WHERE
                    {$wpdb->posts}.post_type = %s AND 
                    DATE({$wpdb->posts}.post_modified) < %s AND
                    {$wpdb->posts}.post_status = 'publish')",
                static::EVENT_TYPE,
                date('Y-m-d')
            );
        }

        return $where;
    }

    public static function event_json_ld($output, $context)
    {
        if ('website' !== $context || !is_singular(self::EVENT_TYPE)) {
            return $output;
        }

        $post = get_queried_object();
        $venue = _te_get_event_venue($post);

        // without the venue we can't output valid JSON-LD, so skip it
        if (apply_filters('te_disable_event_json_ld', !$venue, $post)) {
            return $output;
        }

        $output = array(
            '@context'      => 'http://schema.org',
            '@type'         => 'Event',
            'name'          => strip_tags($post->post_title),
            'startDate'     => te_get_end_date(DATE_ISO8601, $post),
            'location'      => array(
                '@type'         => 'Place',
                'name'          => strip_tags($venue->post_title),
                'address'       => array(
                    '@type'             => 'PostalAddress',
                    'streetAddress'     => te_get_event_street1($post),
                    'addressLocality'   => te_get_event_city($post),
                    'addressRegion'     => te_get_event_state($post),
                    'addressCountry'    => te_get_event_country($post),
                    'postalCode'        => te_get_event_postal($post),
                ),
            ),
        );

        if ($vurl = te_get_event_venue_url($post)) {
            $output['location']['sameAs'] = $vurl;
        }

        if ($street2 = te_get_event_street1($post)) {
            $output['location']['address']['streetAddress'] .= ", {$street2}";
        }

        return $output;
    }
} // end class Event
