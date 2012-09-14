<?php
/**
 * Admin area functionality for the artist type.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class ArtistAdmin extends Artist
{
    const FIELD = 'pmgte_artist_info';

    const NONCE = 'pmg_te_artist_nonce_';

    public static $ff = null;

    public static function init()
    {
        add_action(
            'add_meta_boxes_' . self::ARTIST_TYPE,
            array(__CLASS__, 'meta_boxes')
        );

        add_action(
            'save_post',
            array(__CLASS__, 'save'),
            10,
            2
        );

        add_action(
            'admin_init',
            array(__CLASS__, 'setup')
        );

        add_filter(
            'manage_edit-' . self::ARTIST_TYPE . '_columns',
            array(__CLASS__, 'add_columns')
        );

        add_action(
            'manage_' . self::ARTIST_TYPE . '_posts_custom_column',
            array(__CLASS__, 'column_cb'),
            10,
            2
        );
    }

    public static function setup()
    {
        $f = new FieldFactory(self::FIELD, 'post');

        $f->add_field('artist_url', array(
            'label'     => __('Artist Website', 'the-event'),
            'cleaners'  => array('esc_url_raw')
        ));

        self::$ff = $f;
    }

    public static function meta_boxes($post)
    {
        add_meta_box(
            'te-artist-information',
            __('Artist Information', 'the-event'),
            array(__CLASS__, 'meta_box_cb'),
            self::ARTIST_TYPE,
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
        if(static::ARTIST_TYPE != $post->post_type)
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
            'cb'    => '<input type="checkbox" />',
            'title' => __('Artist Name', 'the-event'),
            'site'  => __('Website', 'the-event'),
        );
    }

    public static function column_cb($col, $post_id)
    {
        $m = Meta::instance('post');

        switch($col)
        {
            case 'site':
                if($s = $m->get($post_id, 'artist_url'))
                {
                    printf(
                        '<a href="%1$s" target="_blank">%1$s</a>',
                        esc_url($s)
                    );
                }
                else
                {
                    esc_html_e('No Website','the-event');
                }
            break;
            // maybe there will be more columsn later!
        }
    }
} // end ArtistAdmin
