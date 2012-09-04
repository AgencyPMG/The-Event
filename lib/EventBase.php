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
     * Event category key.
     *
     * @since   0.1
     */
    const EVENT_CAT = 'pmg_event_cat';

    /**
     * Event tag key.
     *
     * @since   0.1
     */
    const EVENT_TAG = 'pmg_event_tag';

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

    protected static function gen_type_labels($singular, $plural)
    {
        $labels = array(
            'name'              => $plural,
            'singular_name'     => $singular,
            'add_new'           => sprintf(__('New %s', 'the-event'), $singular),
            'all_items'         => sprintf(__('All %s', 'the-event'), $plural),
            'edit_item'         => sprintf(__('Edit %s', 'the-event'), $singular),
            'view_item'         => sprintf(__('View %s', 'the-event'), $singular),
            'search_items'      => sprintf(__('Search %s', 'the-event'), $plural),
            'not_found'         => sprintf(__('No %s Found', 'the-event'), $plural),
            'parent_item_colon' => sprintf(__('Parent %s:', 'the-event'), $singualr)
        );

        $labels['add_new_item'] = $labels['add_new'];
        $labels['new_item'] = $labels['add_new'];
        $labels['not_found_in_trash'] = $labels['not_found'];

        return $labels;
    }

    protected static function gen_tax_labels($singular, $plural)
    {
        $labels = array(
            'name'                  => $plural,
            'singular_name'         => $singular,
            'search_items'          => sprintf(__('Search %s', 'the-event'), $plural),
            'popular_items'         => sprintf(__('Popular %s', 'the-event'), $plural),
            'all_items'             => sprintf(__('All %s', 'the-event'), $plural),
            'parent_item'           => sprintf(__('Parent %s', 'the-event'), $singular),
            'parent_item_colon'     => sprintf(__('Parent %s:', 'the-event'), $singular),
            'edit_item'             => sprintf(__('Edit %s', 'the-event'), $singular),
            'update_item'           => sprintf(__('Update $s', 'the-event'), $singular),
            'add_new_item'          => sprintf(__('New %s', 'the-event'), $singular),
            'new_item_name'         => sprintf(__('New %s Name', 'the-event'), $singular),
            'separate_items_with_commas' => sprintf(__('Seperate %s with commas', 'the-event'), strtolower($plural)),
            'add_or_remove_items'   => sprintf(__('Add or Remove %s', 'the-event'), $plural),
            'choose_from_most_used' => sprintf(__('Choose from most used %s', 'the-event'), strtolower($plural)),
        );

        return $labels;
    }
}
