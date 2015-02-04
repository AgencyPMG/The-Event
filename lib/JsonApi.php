<?php
/**
 * Contains all the functionality for the event post type that needs to work on
 * both the admin and front end.
 *
 * @copyright       2015 PMG
 * @package         PMG\TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

/**
 * Integration with the JSON Rest API Plugin.
 *
 * @since   0.2
 */
class JsonApi extends EventBase
{
    public static function init()
    {
        add_filter('json_endpoints', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes($routes)
    {
        $routes['/events'] = array(
            array(
                array(__CLASS__, 'get_events'),
                \WP_JSON_Server::READABLE,
            ),
        );
        $routes['/events/(?P<id>\d+)'] = array(
            array(
                array(__CLASS__, 'get_event'),
                \WP_JSON_Server::READABLE,
            ),
        );

        return $routes;
    }

    public static function get_events()
    {
        add_filter('posts_where', array(__CLASS__, 'remove_old'), 10, 2);
        $events = apply_filters('the_event_json_collection', get_posts(array(
            'post_type'         => self::EVENT_TYPE,
            'nopaging'          => true,
            'orderby'           => 'post_date post_title',
            'order'             => 'ASC',
            'suppress_filters'  => false,
        )));
        remove_filter('posts_where', array(__CLASS__, 'remove_old'), 10);

        return array_map(array(__CLASS__, 'prepare_event'), $events);
    }

    public static function get_event($id)
    {
        $event = apply_filters('the_event_json_single', get_post($id), $id);
        if (!$event || self::EVENT_TYPE !== $event->post_type) {
            return new \WP_Error('the_event:invalid_id', sprintf(
                __('Event %d does not exist', 'the-event'),
                $id
            ), array('status' => 404));
        }

        return static::prepare_event($event);
    }

    public static function remove_old($where, $q)
    {
        global $wpdb;

        if ($q->get('post_type') === self::EVENT_TYPE) {
            $where .= $wpdb->prepare(
                " AND DATE({$wpdb->posts}.post_modified) >= %s",
                date('Y-m-d')
            );
        }

        return $where;
    }

    public static function prepare_event($event)
    {
        $m = Meta::instance('post');
        $out = array(
            'id'            => $event->ID,
            'title'         => $event->post_title,
            'summary'       => $event->post_excerpt,
            'description'   => $event->post_content,
            'start_date'    => $event->post_date,
            'end_date'      => $event->post_modified,
            'external_link' => $m->get($event->ID, 'external_url') ?: null,
            'tickets'       => array(
                'link'  => $m->get($event->ID, 'ticket_url') ?: null,
                'cost'  => $m->get($event->ID, 'cost') ?: null,
            ),
            'artists'       => array(),
            'venue'         => new \stdClass, // make sure this renders as {}
        );

        if (!empty($event->artists)) {
            foreach ($event->artists as $artist) {
                $out['artists'][] = array(
                    'name'  => $artist->post_title,
                    'link'  => $m->get($artist->ID, 'artist_url') ?: null,
                );
            }
        }

        if (!empty($event->venues)) {
            $venue = $event->venues[0];
            $vid = $venue->ID;
            $out['venue'] = array(
                'name'      => $venue->post_title,
                'city'      => $m->get($vid, 'venue_city') ?: null,
                'region'    => $m->get($vid, 'venue_state') ?: null,
                'country'   => $m->get($vid, 'venue_country') ?: null,
                'postal'    => $m->get($vid, 'venue_zip') ?: null,
                'street'    => $m->get($vid, 'venue_street_1') ?: null,
                'street2'   => $m->get($vid, 'venue_street_2') ?: null,
            );
        }

        if (function_exists('json_url')) {
            $out['links'] = array(
                'self'          => json_url(sprintf('/events/%d', $event->ID)),
                'collection'    => json_url('/events'),
            );
        }


        return apply_filters('the_event_json_event', $out, $event);
    }
}
