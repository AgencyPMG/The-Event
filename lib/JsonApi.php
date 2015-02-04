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
        add_filter('posts_where', array(__CLASS__, 'remove_old'));
        $events = apply_filters('the_event_json_collection', get_posts(array(
            'post_type'         => self::EVENT_TYPE,
            'nopaging'          => true,
            'orderby'           => 'post_date post_title',
            'order'             => 'ASC',
            'suppress_filters'  => false,
        )));
        remove_filter('posts_where', array(__CLASS__, 'remove_old'));

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

    public static function remove_old($where)
    {
        global $wpdb;

        $where .= $wpdb->prepare(
            " AND DATE({$wpdb->posts}.post_modified) >= %s",
            date('Y-m-d')
        );

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
        );

        return apply_filters('the_event_json_event', $out, $event);
    }
}
