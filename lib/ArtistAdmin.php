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
    public static function init()
    {
        add_action(
            'add_meta_boxes_' . self::ARTIST_TYPE,
            array(__CLASS__, 'meta_boxes')
        );
    }

    public static function meta_boxes($post)
    {
    }
} // end ArtistAdmin
