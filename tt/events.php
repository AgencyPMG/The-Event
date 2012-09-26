<?php
/**
 * Template tags for events.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

!defined('ABSPATH') && exit;

use PMG\TheEvent\Meta;
use PMG\TheEvent\P2PIntegration;

function te_get_start_date($fmt, $post)
{
    return apply_filters('te_get_start_date',
        mysql2date($fmt, $post->post_date), $post->ID);
}

function te_start_date($fmt=null)
{
    global $post;

    is_null($fmt) && $fmt = get_option('date_format');

    echo apply_filters('te_start_date',
        te_get_start_date($fmt, $post), $post->ID);
}

function te_get_end_date($fmt, $post)
{
    return apply_filters('te_get_end_date',
        mysql2date($fmt, $post->post_date), $post->ID);
}

function te_end_date($fmt=null)
{
    global $post;

    is_null($fmt) && $fmt = get_option('date_format');

    echo apply_filters('te_start_date',
        te_get_start_date($fmt, $post), $post->ID);
}

function te_get_ticket_url($post)
{
    return apply_filters('te_get_ticket_url',
        Meta::instance('post')->get($post->ID, 'ticket_url'), $post->ID);
}

function te_ticket_url()
{
    global $post;

    echo apply_filters('te_ticket_url',
        te_get_ticket_url($post), $post->ID);
}

function te_get_ticket_price($post)
{
    return apply_filters('te_get_ticket_price',
        Meta::instance('post')->get($post->ID, 'cost'), $post->ID);
}

function te_ticket_price()
{
    global $post;

    echo apply_filters('te_ticket_price',
        te_get_ticket_price($post), $post->ID);
}

/**
 * Helper to get the currently associated venue
 *
 * @since   0.1
 * @internal
 */
function _te_get_event_venue($post)
{
    $venue = false;

    if(isset($post->venues) && count($post->venues))
    {
        $venue = $post->venues[0];
    }
    else
    {
        $venues = array();

        if(function_exists('p2p_type'))
        {
            $q = p2p_type(P2PIntegration::E_TO_V)->get_connected($post->ID);
            if($q->have_posts())
            {
                $venue = $q->posts[0];
                $venues = $q->posts;
            }
        }

        // set this so we don't have to through the above again.
        $post->venues = $venues;
    }

    return $venue;
}

/**
 * Helper to get meta from a current venue or return false.
 *
 * @since   0.1
 * @internal
 */
function _te_get_venue_meta($post, $mk)
{
    $venue = _te_get_event_venue($post);

    $res = false;

    if($venue)
        $res = Meta::instance('post')->get($venue->ID, $mk);

    return $res;
}

function te_get_event_street1($post)
{
    return apply_filters('te_get_event_street1',
        _te_get_venue_meta($post, 'venue_street_1'), $post->ID);
}

function te_event_street1()
{
    global $post;

    echo apply_filters('te_event_street1',
        te_get_event_street1($post), $post->ID);
}

function te_get_event_street2($post)
{
    return apply_filters('te_get_event_street2',
        _te_get_venue_meta($post, 'venue_street_1'), $post->ID);
}

function te_event_street2()
{
    global $post;

    echo apply_filters('te_event_street2',
        te_get_event_street2($post), $post->ID);
}

function te_get_event_city($post)
{
    return apply_filters('te_get_event_city',
        _te_get_venue_meta($post, 'venue_city'), $post->ID);
}

function te_event_city()
{
    global $post;

    echo apply_filters('te_event_city', te_get_event_city($post), $post->ID);
}

function te_get_event_state($post)
{
    return apply_filters('te_get_event_state',
        _te_get_venue_meta($post, 'venue_state'), $post->ID);
}

function te_event_state()
{
    global $post;

    echo apply_filters('te_event_state', te_get_event_state($post), $post->ID);
}

function te_get_event_postal($post)
{
    return apply_filters('te_get_event_postal',
        _te_get_venue_meta($post, 'venue_zip'), $post->ID);
}

function te_event_postal()
{
    global $post;

    echo apply_filters('te_event_postal',
        te_get_event_postal($post), $post->ID);
}

function te_get_event_country($post)
{
    return apply_filters('te_get_event_country',
        _te_get_venue_meta($post, 'venue_country'), $post->ID);
}

function te_event_country()
{
    global $post;

    echo apply_filters('te_event_country',
        te_get_event_country($post), $post->ID);
}

function te_get_event_venue_url($post)
{
    return apply_filters('te_get_event_venue_url',
        _te_get_venue_meta($post, 'venue_url'), $post->ID);
}

function te_event_venue_url()
{
    global $post;

    echo apply_filters('te_event_venue_url',
        te_get_event_venue_url($post), $post->ID);
}

function te_get_event_venue_phone($post)
{
    return apply_filters('te_get_event_venue_phone',
        _te_get_venue_meta($post, 'venue_phone'), $post->ID);
}

function te_event_venue_phone()
{
    global $post;

    echo apply_filters('te_event_venue_phone',
        te_get_event_venue_phone($post), $post->ID);
}
