<?php
/**
 * The main admin options page.
 *
 * @author          Christopher Davis <chris@pmg.co>
 * @copyright       Performance Media Group 2012
 * @since           0.1
 * @package         TheEvent
 * @license         GPLv2
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class AdminOptions extends EventBase
{
    // container for the field factory
    private static $ff = null;

    public static function init()
    {
        add_action(
            'admin_init',
            array(__CLASS__, 'settings')
        );

        add_action(
            'admin_menu',
            array(__CLASS__, 'page')
        );
    }

    public static function settings()
    {
        register_setting(
            self::OPTION,
            self::OPTION,
            array(__CLASS__, 'validate')
        );

        add_settings_section(
            'slugs',
            __('Rewrite Slugs', 'the-event'),
            '__return_false',
            self::OPTION
        );

        $f = new FieldFactory(self::OPTION);

        $f->add_field('event_slug', array(
            'label'     => __('Event Base', 'the-event'),
            'cleaners'  => array('sanitize_title_with_dashes'),
            'section'   => 'slugs',
            'class'     => 'regular-text',
        ));

        $f->add_field('artist_slug', array(
            'label'     => __('Artist Base', 'the-event'),
            'cleaners'  => array('sanitize_title_with_dashes'),
            'section'   => 'slugs',
            'class'     => 'regular-text',
        ));

        $f->add_field('venue_slug', array(
            'label'     => __('Venue Base', 'the-event'),
            'cleaners'  => array('sanitize_title_with_dashes'),
            'section'   => 'slugs',
            'class'     => 'regular-text',
        ));

        $f->add_settings_fields();

        self::$ff = $f;
    }

    public static function page()
    {
        add_submenu_page(
            add_query_arg('post_type', self::EVENT_TYPE, 'edit.php'),
            __('The Event Options', 'the-event'),
            __('Options', 'the-event'),
            'manage_options',
            'the-event-options',
            array(__CLASS__, 'page_cb')
        );
    }

    public static function page_cb()
    {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e('The Event Options', 'the-event'); ?></h2>
            <?php settings_errors(self::OPTION); ?>
            <form method="post" action="<?php echo admin_url('options.php'); ?>">
                <?php
                settings_fields(self::OPTION);
                do_settings_sections(self::OPTION);
                submit_button(__('Save Settings', 'the-event'));
                ?>
            </form>
        </div>
        <?php
    }

    public static function validate($in)
    {
        $clean = self::$ff->validate($in);

        add_settings_error(
            self::OPTION,
            'the-event-updated',
            __('Settings Saved', 'the-event'),
            'updated'
        );

        return $clean;
    }
}
