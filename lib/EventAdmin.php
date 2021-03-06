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

        add_action(
            'load-edit.php',
            array(__CLASS__, 'load_edit')
        );

        add_filter(
            'wp_insert_post_data',
            array(__CLASS__, 'change_dates'),
            10,
            2
        );

        add_action('load-post.php', array(__CLASS__, 'load_edit_screen'));
        add_action('load-post-new.php', array(__CLASS__, 'load_edit_screen'));
    }

    public static function fields()
    {
        $f = new FieldFactory('pmgte_event_info', 'post');

        $f->add_field('ticket_url', array(
            'label'     => __('Ticket URL', 'the-event'),
            'cleaners'  => array('esc_url_raw'),
        ));

        $f->add_field('external_url', array(
            'label'     => __('External URL', 'the-event'),
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
        ));
        ?>        
        <div id="preview-action">
            <?php
            if ('publish' == $post->post_status) {
                $preview_link = esc_url(get_permalink($post->ID));
                $preview_button = __('Preview Changes', 'the-event');
            } else {
                $preview_link = get_permalink($post->ID);
                if (is_ssl()) {
                    $preview_link = str_replace('http://', 'https://', $preview_link);
                }
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
        <div>
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
        <div>
            <p class="te-start-time">
                <strong><?php esc_html_e('Start Date', 'the-event'); ?></strong>
                <br />
                <?php self::date_fields($post->post_date); ?>
            </p>
            <p class="te-end-time">
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
        else
        {
            // something is wrong.  Just call save with a blank array
            // which will delete all the meta
            self::$ff->save($post_id, array());
        }
    }

    public static function load_edit()
    {
        $pt = isset($_GET['post_type']) && $_GET['post_type'] ?
            $_GET['post_type'] : false;

        if(!$pt || $pt != static::EVENT_TYPE)
            return;

        add_filter(
            'display_post_states',
            array(__CLASS__, 'change_states')
        );

        add_filter(
            'views_' . get_current_screen()->id,
            array(__CLASS__, 'views')
        );

        add_filter(
            'manage_edit-' . static::EVENT_TYPE . '_columns',
            array(__CLASS__, 'add_columns')
        );

        add_filter(
            'manage_edit-' . static::EVENT_TYPE . '_sortable_columns',
            array(__CLASS__, 'sortable')
        );

        add_action(
            'manage_' . self::EVENT_TYPE . '_posts_custom_column',
            array(__CLASS__, 'column_cb'),
            10,
            2
        );
    }

    public static function change_states($s)
    {
        if(isset($s['draft']))
            $s['draft'] = __('Inactive', 'the-event');

        if(isset($s['publish']))
            $s['publish'] = __('Active', 'the-event');

        return $s;
    }

    public static function views($views)
    {
        if(isset($views['publish']))
        {
            $views['publish'] = sprintf(
                '<a href="%s" %s>%s</span></a>',
                add_query_arg(array(
                    'post_type'     => static::EVENT_TYPE,
                    'post_status'   => 'publish',
                ), admin_url('edit.php')),
                isset($_GET['post_status']) && 'publish' == $_GET['post_status'] ?
                    'class="current"' : '',
                esc_html__('Active', 'the-event')
            );
        }

        if(isset($views['draft']))
        {
            $views['draft'] = sprintf(
                '<a href="%s" %s>%s</span></a>',
                add_query_arg(array(
                    'post_type'     => static::EVENT_TYPE,
                    'post_status'   => 'draft',
                ), admin_url('edit.php')),
                isset($_GET['post_status']) && 'draft' == $_GET['post_status'] ?
                    'class="current"' : '',
                esc_html__('Inactive', 'the-event')
            );
        }

        return $views;
    }

    public static function add_columns($cols)
    {
        $cols = array(
            'cb'     => '<input type="checkbox" />',
            'title'  => __('Event', 'the-event'),
            'e_date' => __('Event Date', 'the-event'),
        );

        if(function_exists('p2p_type'))
        {
            $cols['artist'] = __('Artist/Presenter', 'the-event');
            $cols['venue'] = __('Venue', 'the-event');
        }

        return $cols;
    }

    public static function sortable($cols)
    {
        $cols['e_date'] = 'date';
        return $cols;
    }

    public static function column_cb($col, $post_id)
    {
        // there has to be a way to use p2p_type()->each_connected here...

        $post = get_post($post_id);
        switch($col)
        {
            case 'e_date':
                echo date_i18n(
                    get_option('date_format'),
                    strtotime($post->post_date)
                );
                break;
            case 'artist':
                $artists = p2p_type(P2PIntegration::E_TO_A)->get_connected($post);
                if($artists->have_posts())
                {
                    echo implode(__(', ', 'the-event'), array_map(function($i) {
                        return esc_html($i->post_title);
                    }, $artists->posts));
                }
                else
                {
                    esc_html_e('No Artists', 'the-event');
                }
                break;
            case 'venue':
                $venues = p2p_type(P2PIntegration::E_TO_V)->get_connected($post);
                if($venues->have_posts())
                {
                    echo implode(__(', ', 'the-event'), array_map(function($i) {
                        return esc_html($i->post_title);
                    }, $venues->posts));
                }
                else
                {
                    esc_html_e('No Venue', 'the-event');
                }
                break;
        }
    }

    public static function change_dates($data, $postarr)
    {
        if (static::EVENT_TYPE != $data['post_type']) {
            return $data;
        }

        if(
            !isset($_POST[self::DATE_NONCE]) ||
            !wp_verify_nonce($_POST[self::DATE_NONCE], self::DATE_NONCE . $postarr['ID'])
        ) {
            return $data;
        }

        $types = array(
            'aa' => 'Y',
            'mm' => 'm',
            'jj' => 'd',
            'hh' => 'H',
            'mn' => 'i'
        );

        foreach (array('start', 'end') as $t) {
            $date = array();
            foreach ($types as $k => $d) {
                $date[$k] = isset($_POST["te_{$t}_{$k}"]) ? $_POST["te_{$t}_{$k}"] : date($d);
            }

            $fmt = sprintf(
                '%s-%s-%s %s:%s:00',
                $date['aa'],
                $date['mm'],
                $date['jj'],
                $date['hh'],
                $date['mn']
            );

            // this is ridiculous, but I guess it's how one does form validation
            // in WordPress? At least it's what WordPress itself does for post
            // dates. Awful.
            if (!wp_checkdate($date['mm'], $date['jj'], $date['aa'], $fmt)) {
                wp_die(
                    sprintf(__("You've entered an invalid %s date.", 'the-event'), $t),
                    __('Invalid Date', 'the-event'),
                    array('response' => 400)
                );
            }

            if ('start' == $t) {
                $data['post_date'] = $fmt;
            } else {
                $data['post_modified'] = $fmt;
            }
        }

        // WordPress likes to change this from what was actually submitted based
        // on the post publish date. Since we use that to do our own date stuff
        // we need to make sure to change it back
        if (
            !empty($postarr['post_status']) &&
            in_array($postarr['post_status'], array('publish', 'draft', 'private'))
        ) {
            $data['post_status'] = $postarr['post_status'];
        }

        return $data;
    }

    public static function load_edit_screen()
    {
        $screen = get_current_screen();
        if (!isset($screen->post_type) || self::EVENT_TYPE !== $screen->post_type) {
            return;
        }

        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
    }

    public static function enqueue()
    {
        wp_enqueue_script(
            'pmg_te_edit_event',
            PMG_TE_URL.'assets/js/events.js',
            array('jquery'),
            self::ASSET_VERSION
        );

        wp_enqueue_style(
            'pmg_te_edit_event',
            PMG_TE_URL.'assets/css/events.css',
            array(),
            self::ASSET_VERSION
        );
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

        $month = "<select name='{$prefix}_mm' class='mm'>";
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

        $day = '<input type="text" name="' . $prefix . '_jj" class="jj" ' .
            'value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';

        $year = '<input type="text" name="' . $prefix . '_aa" class="aa" ' .
            'value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';

        $hour = '<input type="text" name="' . $prefix . '_hh" class="hh" ' .
            'value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';

        $minute = '<input type="text" name="' . $prefix . '_mn" class="mn" ' .
            'value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

        /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
        printf(
            __('%1$s%2$s, %3$s @ %4$s : %5$s', 'the-event'),
            $month, $day, $year, $hour, $minute
        );
    }
} // end EventAdmin
