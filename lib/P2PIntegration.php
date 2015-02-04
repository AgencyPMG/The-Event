<?php
/**
 * Posts to post integration.  Sets up all the connections and alters the
 * queries where necessary.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class P2PIntegration extends EventBase
{
    const E_TO_A = 'event_to_artist';

    const E_TO_V = 'event_to_venue';

    public static function init()
    {
        add_action(
            'plugins_loaded',
            array(__CLASS__, '_init')
        );
    }

    public static function _init()
    {
        if(!function_exists('p2p_register_connection_type'))
        {
            add_action(
                'admin_notices',
                array(__CLASS__, 'notice')
            );
            return;
        }

        add_action(
            'p2p_init',
            array(__CLASS__, 'connections')
        );

        add_action(
            'loop_start',
            array(__CLASS__, 'load_connected')
        );

        add_filter(
            'p2p_new_post_args',
            array(__CLASS__, 'publish_new')
        );

        add_filter(
            'the_event_json_collection',
            array(__CLASS__, 'add_json_collection_connections')
        );

        add_filter(
            'the_event_json_single',
            array(__CLASS__, 'add_json_single_connections')
        );
    }

    public static function notice()
    {
        ?>
        <div id="the-event-notice" class="error">
            <p>
                <?php
                printf(
                    __('The Event requires %s.  Please install it.'),
                    '<a href="http://wordpress.org/extend/plugins/posts-to-posts/">Posts 2 Posts</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    public static function connections()
    {
        p2p_register_connection_type(array(
            'name'          => static::E_TO_A,
            'from'          => static::EVENT_TYPE,
            'to'            => static::ARTIST_TYPE,
            'admin_box'     => array(
                'show'          => 'from',
                'context'       => 'normal',
            ),
            'title'         => array(
                'from'         => __('Artists or Presenters', 'the-event')
            ),
            'to_labels'   => array( // why is this to_labels?
                'create'        => __('Add Artists', 'the-event')
            )
        ));

        p2p_register_connection_type(array(
            'name'          => static::E_TO_V,
            'from'          => static::EVENT_TYPE,
            'to'            => static::VENUE_TYPE,
            'cardinality'   => 'many-to-one',
            'admin_box'     => array(
                'show'          => 'from',
                'context'       => 'normal'
            ),
            'title'         => array(
                'from'          => __('Venue', 'the-event')
            ),
            'to_labels'     => array( // why is this to_labels
                'create'        => __('Add Venue', 'the-event')
            )
        ));
    }

    public static function load_connected($q)
    {
        if(is_admin() || !$q->is_main_query())
            return;

        if(
            is_singular(static::EVENT_TYPE) ||
            is_post_type_archive(static::EVENT_TYPE) ||
            is_tax(array(static::EVENT_CAT, static::EVENT_TAG))
        ) {
            static::add_artists($q);
            static::add_venues($q);
        }
        elseif(is_singular(static::ARTIST_TYPE))
        {
            p2p_type(static::E_TO_A)->each_connected($q, array(
                'meta_key' => static::get_key('event_date'),
                'orderby'  => 'meta_value',
                'order'    => 'DESC'
            ), 'events');
        }
        elseif(is_singular(static::VENUE_TYPE))
        {
            p2p_type(static::E_TO_V)->each_connected($q, array(
                'meta_key' => static::get_key('event_date'),
                'orderby'  => 'meta_value',
                'order'    => 'DESC'
            ), 'events');
        }
    }

    public static function publish_new($args)
    {
        if(
            static::VENUE_TYPE == $args['post_type'] ||
            static::ARTIST_TYPE == $args['post_type']
        ) $args['post_status'] = 'publish';

        return $args;
    }

    public static function add_json_collection_connections($events)
    {
        static::add_artists($events);
        static::add_venues($events);

        return $events;
    }

    public static function add_json_single_connections($event)
    {
        static::add_artists(array($event));
        static::add_venues(array($event));

        return $event;
    }

    private static function add_artists($q)
    {
        p2p_type(static::E_TO_A)->each_connected($q, array(
            'orderby'   => 'title',
            'nopaging'  => true
        ), 'artists');
    }

    private static function add_venues($q)
    {
        p2p_type(static::E_TO_V)->each_connected($q, array(), 'venues');
    }
} // end class P2PIntegration
