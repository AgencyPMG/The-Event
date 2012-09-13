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
    const DATE_NONCE = 'event_date_nonce_';

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

        add_filter(
            'wp_insert_post_data',
            array(__CLASS__, 'change_dates'),
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

        add_meta_box(
            'event-publish-div',
            __('Date & Status', 'the-event'),
            array(__CLASS__, 'publish_cb'),
            static::EVENT_TYPE,
            'side',
            'high'
        );

        remove_meta_box(
            'submitdiv',
            static::EVENT_TYPE,
            'side'
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

    public static function publish_cb($post)
    {
        $type = get_post_type($post->post_type);
        $stati = apply_filters('the_event_stati', array(
            'publish' => __('Active', 'the-event'),
            'draft'   => __('Inactive', 'the-event'),
            'private' => __('Private', 'the-event'),
            'trash'   => __('Trashed', 'the-event'),
        ));
        ?>        
        <div id="preview-action" class="misc-pub-section">
            <?php
            if('publish' == $post->post_status)
            {
                $preview_link = esc_url(get_permalink($post->ID));
                $preview_button = __('Preview Changes', 'the-event');
            }
            else
            {
                $preview_link = get_permalink($post->ID);
                if (is_ssl() )
                    $preview_link = str_replace('http://', 'https://', $preview_link);
                $preview_link = esc_url(apply_filters(
                    'preview_post_link',
                    add_query_arg('preview', 'true', $preview_link)
                ));
                $preview_button = __('Preview', 'the-event');
            }
            ?>
            <p><a class="button"
                href="<?php echo $preview_link; ?>"
                target="wp-preview"
                id="post-preview" 
                tabindex="4"><?php echo $preview_button; ?></a></p>
            <input type="hidden" name="wp-preview" id="wp-preview" value="" />
        </div>
        <div class="misc-pub-section">
                <label for="the_event_status">
                    <strong><?php _e('Status:', 'the-event'); ?></strong>
                </label>
                <br />
                <select name="post_status" id="the_event_status">
                    <option value="">---</option>
                    <?php foreach($stati as $s => $label): ?>
                        <option value="<?php echo esc_attr($s); ?>"
                            <?php selected($post->post_status, $s); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
        </div>
        <div class="misc-pub-section timestampdiv">
            <p>
                <strong><?php esc_html_e('Start Date', 'the-event'); ?></strong>
                <br />
                <?php self::date_fields($post->post_date); ?>
            </p>
            <p>
                <strong><?php esc_html_e('End Date', 'the-event'); ?></strong>
                <br />
                <?php self::date_fields($post->post_modified, 'end'); ?>
            </p>
        </div>
        <p>
            <input name="save" type="submit" 
                class="button-primary"
                id="publish" tabindex="5"
                accesskey="p" value="<?php esc_attr_e('Save', 'the-event'); ?>" />
        </p>
        <?php
        wp_nonce_field(
            self::DATE_NONCE . $post->ID,
            self::DATE_NONCE,
            false
        );
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

    public static function change_dates($data, $postarr)
    {
        if(static::EVENT_TYPE != $data['post_type'])
            return $data;

        if(
            !isset($_POST[self::DATE_NONCE]) ||
            !wp_verify_nonce($_POST[self::DATE_NONCE], self::DATE_NONCE . $postarr['ID'])
        ) return $data;

        $types = array(
            'aa' => 'Y',
            'mm' => 'm',
            'jj' => 'd',
            'hh' => 'H',
            'mn' => 'i'
        );

        foreach(array('start', 'end') as $t)
        {
            $date = array();
            foreach($types as $k => $d)
                $date[$k] = isset($_POST["te_{$t}_{$k}"]) ? $_POST["te_{$t}_{$k}"] : date($d);

            $fmt = sprintf(
                '%s-%s-%s %s:%s:00',
                $date['aa'],
                $date['mm'],
                $date['jj'],
                $date['hh'],
                $date['mn']
            );

            if('start' == $t)
                $data['post_date'] = $fmt;
            else
                $data['post_modified'] = $fmt;
        }

        return $data;
    }

    protected static function date_fields($post_date, $prefix='start')
    {
        global $wp_locale;

        $prefix = 'te_' . esc_attr($prefix);

        $jj = mysql2date( 'd', $post_date, false );
        $mm = mysql2date( 'm', $post_date, false );
        $aa = mysql2date( 'Y', $post_date, false );
        $hh = mysql2date( 'H', $post_date, false );
        $mn = mysql2date( 'i', $post_date, false );

        $month = "<select name='{$prefix}_mm'>";
        for ( $i = 1; $i < 13; $i = $i +1 ) {
            $monthnum = zeroise($i, 2);
            $month .= '<option value="' . $monthnum . '"';
            if ( $i == $mm )
                $month .= ' selected="selected"';
            $month .= '>' . sprintf(
                __('%1$s-%2$s', 'the-event'),
                $monthnum, 
                $wp_locale->get_month_abbrev($wp_locale->get_month($i))
            ) . "</option>";
        }
        $month .= '</select>';

        $day = '<input type="text" name="' . $prefix . '_jj" id="jj" ' .
            'value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';

        $year = '<input type="text" name="' . $prefix . '_aa" id="aa" ' .
            'value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';

        $hour = '<input type="text" name="' . $prefix . '_hh" id="hh" ' .
            'value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';

        $minute = '<input type="text" name="' . $prefix . '_mn" id="mn" ' .
            'value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

        /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
        printf(
            __('%1$s%2$s, %3$s @ %4$s : %5$s', 'the-event'),
            $month, $day, $year, $hour, $minute
        );
    }
} // end EventAdmin
