<?php
/**
 * Event admin functionality.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class EventAdmin extends Event
{
    const NONCE = 'event_meta_nonce_';

    public static $ff = null;

    public static function init()
    {
        add_action(
            'admin_init',
            array(__CLASS__, 'fields')
        );

        add_action(
            'add_meta_boxes_' . static::EVENT_TYPE,
            array(__CLASS__, 'meta_box')
        );

        add_action(
            'save_post',
            array(__CLASS__, 'save'),
            10,
            2
        );
    }

    public static function fields()
    {
        $f = new FieldFactory('pmgte_event_info', 'post', 'the_event');

        $f->add_field('ticket_url', array(
            'label'     => __('Ticket URL', 'the-event'),
            'cleaners'  => array('esc_url_raw'),
        ));

        $f->add_field('cost', array(
            'label'     => __('Ticket Price', 'the-event'),
        ));

        self::$ff = $f;
    }

    public static function meta_box($post)
    {
        add_meta_box(
            'the-event-information',
            __('Event Information', 'the-event'),
            array(__CLASS__, 'meta_box_cb'),
            static::EVENT_TYPE,
            'normal',
            'high'
        );
    }

    public static function meta_box_cb($post)
    {
        wp_nonce_field(
            self::NONCE . $post->ID,
            self::NONCE,
            false
        );

        self::$ff->render($post->ID);
    }

    public static function save($post_id, $post)
    {
        if(static::EVENT_TYPE != $post->post_type)
            return;

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if(
            !isset($_POST[self::NONCE]) ||
            !wp_verify_nonce($_POST[self::NONCE], self::NONCE . $post_id)
        ) return;

        if(!current_user_can('edit_page', $post_id))
            return;

        $k = self::$ff->get_opt();
        if(isset($_POST[$k]))
        {
            self::$ff->save($post_id, $_POST[$k]);
        }
    }
} // end EventAdmin
