<?php
/**
 * A class for the rest of the plugin.  Contains some useful methods as well as
 * class constants.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class EventBase
{
    /**
     * Event post type.
     *
     * @since   0.1
     */
    const EVENT_TYPE = 'pmg_event';

    /**
     * Artist/presenter post type.
     *
     * @since   0.1
     */
    const ARTIST_TYPE = 'pmg_artist';

    /**
     * Venue post type.
     *
     * @since   0.1
     */
    const VENUE_TYPE = 'pmg_venue';

    /**
     * The option key.
     *
     * @since   0.1
     */
    const OPTION = 'pmg_the_event_options';

    /**
     * Meta key prefix.
     *
     * @since   0.1
     */
    const PREFIX = '_the_event_';

    public static function opt($key, $default='')
    {
        $opts = get_option(static::OPTION, array());
        return isset($opts[$key]) ? $opts[$key] : $default;
    }

    public static function get_key($key)
    {
        return static::PREFIX . $key;
    }

    public static function get_meta($post_id, $key)
    {
        return get_post_meta($post_id, static::get_key($key), true);
    }

    public static function delete_meta($post_id, $key)
    {
        return delete_post_meta($post_id, static::get_key($key));
    }

    public static function update_meta($post_id, $key, $val)
    {
        return update_post_meta($post_id, static::get_key($key), $val);
    }

    protected static function can_edit($type, $nonce_key, $nonce_act, $post)
    {
        if($post->post_type != $type)
            return false;

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return false;

        if(
            !isset($_POST[$nonce_key]) ||
            !wp_verify_nonce($_POST[$nonce_key], $nonce_act)
        ) return false;

        $type = get_post_type($post->post_type);
        if(!current_user_can($type->cap['edit_post'], $post->ID))
            return false;

        return true;
    }
}
