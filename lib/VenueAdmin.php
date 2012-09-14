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

class VenueAdmin extends Venue
{
    const NONCE = 'pmgte_venue_nonce_';

    public static $ff = null;

    public static function init()
    {
        add_action(
            'add_meta_boxes_' . self::VENUE_TYPE,
            array(__CLASS__, 'meta_box')
        );

        add_action(
            'admin_init',
            array(__CLASS__, 'fields')
        );

        add_action(
            'save_post',
            array(__CLASS__, 'save'),
            10,
            2
        );

        add_filter(
            'manage_edit-' . self::VENUE_TYPE . '_columns',
            array(__CLASS__, 'add_columns')
        );

        add_action(
            'manage_' . self::VENUE_TYPE . '_posts_custom_column',
            array(__CLASS__, 'column_cb'),
            10,
            2
        );
    }

    public static function meta_box()
    {
        add_meta_box(
            'pmgte-venue-info',
            __('Venue Info', 'the-event'),
            array(__CLASS__, 'meta_box_cb'),
            self::VENUE_TYPE,
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

    public static function fields()
    {
        $f = new FieldFactory('pmgte_venue_info', 'post');

        $f->add_field('venue_street_1', array(
            'label' => __('Street Address 1', 'the-event'),
        ));

        $f->add_field('venue_street_2', array(
            'label' => __('Street Address 2', 'the-event'),
        ));

        $f->add_field('venue_city', array(
            'label' => __('City', 'the-event'),
        ));

        $f->add_field('venue_state', array(
            'label' => __('State/Region', 'the-event'),
        ));

        $f->add_field('venue_zip', array(
            'label' => __('Postal Code', 'the-event'),
        ));

        $f->add_field('venue_country', array(
            'label' => __('Country', 'the-event'),
        ));

        $f->add_field('venue_url', array(
            'label'    => __('Website', 'the-event'),
            'cleaners' => array('esc_url_raw'),
        ));

        $f->add_field('venue_phone', array(
            'label' => __('Phone', 'the-event'),
        ));

        self::$ff = $f;
    }

    public static function save($post_id, $post)
    {
        if(static::VENUE_TYPE != $post->post_type)
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
        else
        {
            // something is wrong.  Just call save with a blank array
            // which will delete all the meta
            self::$ff->save($post_id, array());
        }
    }

    public static function add_columns($cols)
    {
        return array(
            'cb'      => '<input type="checkbox" />',
            'title'   => __('Venue Name', 'the-event'),
            'address' => __('Address', 'the-event'),
            'website' => __('Website', 'the-event')
        );
    }

    public static function column_cb($col, $post_id)
    {
        $m = Meta::instance('post');

        switch($col)
        {
            case 'address':
                if($street = $m->get($post_id, 'venue_street_1'))
                    echo esc_html($street) . '<br />';

                if($street2 = $m->get($post_id, 'venue_street_2'))
                    echo esc_html($street2) . '<br />';

                if($city = $m->get($post_id, 'venue_city'))
                    echo esc_html($city);

                if($country = $m->get($post_id, 'venue_country'))
                {
                    if($city)
                        echo ', ';
                    echo esc_html($country);
                }
            break;
            case 'website':
                if($url = $m->get($post_id, 'venue_url'))
                {
                    printf(
                        '<a href="%1$s" target="_blank">%1$s</a>',
                        esc_url($url)
                    );
                }
                else
                {
                    esc_html_e('No Website', 'the-event');
                }
            break;
        }
    }
} // end VenueAdmin
