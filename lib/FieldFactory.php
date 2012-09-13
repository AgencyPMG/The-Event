<?php
/**
 * Generic callbacks for input fields.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     TheEvent
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class FieldFactory
{
    /**
     * setting name
     * 
     * @since   1.0
     * @access  private
     */
    private $opt = null;

    /**
     * The meta data type.  Useed internally to fetch data for items outside
     * the settings API.
     *
     * @since   1.0
     * @access  private
     * @var     string
     */
    private $type = null;

    /**
     * The meta prefix.  Used internally to fetch data for items outside the 
     * settings API.
     *
     * @since   1.0
     * @access  private
     * @var     string
     */
    private $prefix = null;

    /**
     * Container for the meta factory.
     *
     * @since   1.0
     * @access  private
     * @var
     */
    private $meta = null;

    /**
     * Container for registered form fields
     *
     * @since   1.0
     * @access  private
     */
    private $fields;

    public function __construct($opt, $meta_type=false, $meta_prefix=null)
    {
        $this->opt = $opt;
        $this->type = $meta_type;
        $this->prefix = $meta_prefix;

        if($this->type && $this->prefix)
            $this->meta = new MetaFactory($this->type, $this->prefix);
    }

    protected function error($msg)
    {
        echo esc_html($msg);
    }

    protected function gen_name($key)
    {
        return is_null($this->opt) ? $key : sprintf('%s[%s]', $this->opt, $key);
    }

    protected function input($type, $value, $key, $cls='widefat')
    {
        $name = $this->gen_name($key);
        printf(
            '<input type="%1$s" class="%2$s" name="%3$s" id="%3$s" value="%4$s" />',
            esc_attr($type),
            esc_attr($cls),
            esc_attr($name),
            esc_attr($value)
        );
    }

    protected function text_input($value, $key, $cls='widefat')
    {
        $this->input('text', $value, $key, $cls);
    }

    protected function password_input($value, $key, $cls='widefat')
    {
        $this->input('password', $value, $key, $cls);
    }

    protected function textarea($value, $key, $cls='widefat', $args)
    {
        printf(
            '<textarea id="%1$s" name="%1$s" class="%2$s" %3$s>%4$s</textarea>',
            esc_attr($this->gen_name($key)),
            esc_attr($cls),
            esc_textarea($value),
            isset($args['rows']) ? 'rows="' . absint($args['rows']) . '"' : ''
        );
    }

    protected function select($value, $key, $cls='', $args)
    {
        $options = isset($args['options']) ? $args['options'] : array();
        $is_multi = isset($args['multi']) && $args['multi'];
        $name = $this->gen_name($name);

        if($is_multi)
            $name .= '[]';

        printf(
            '<select id="%1$s" name="%1$s" class="%2$s" %3$s>',
            esc_attr($name),
            esc_attr($cls),
            $is_multi ? 'multiple="mulitple"' : ''
        );
        foreach($options as $val => $label)
        {
            if($is_multi)
            {
                if(in_array($val, (array)$value))
                    $s = 'selected="selected"';
                else
                    $s = '';
            }
            else
            {
                $s = selected($val, $value, false);
            }

            printf(
                '<option value="%1$s" %2$s>%2$s</option>',
                esc_attr($val),
                $s
            );
        }
        echo '</select>';
    }

    protected function multiselect($value, $key, $cls, $args)
    {
        $args['multi'] = true;
        $this->select($value, $key, $cls, $args);
    }

    /********** Internals **********/

    /**
     * Set up the field values from the {$prefix}_{$this->type}meta
     *
     * @since   1.0
     * @access  protected
     * @return  null
     */
    protected function setup_values($id)
    {
        foreach($this->fields as $key => $field)
        {
            $this->fields[$key]['value'] = $this->meta->get($id, $key);
        }
    }

    /********** Public API **********/

    /**
     * Spit out a label.
     *
     * @since   1.0
     * @access  public
     * @param   string $id The value to put in the `for` attr
     * @param   string $label The actual label
     */
    public function label($id, $label)
    {
        printf(
            '<label for="%1$s">%2$s</label>',
            esc_attr($id),
            esc_html($label)
        );
    }

    /**
     * Generic field callback.  Checks for some things then dispatches the call
     * to an appropriate method.
     *
     * @since   1.0
     * @access  public
     * @return  null
     */
    public function cb($args)
    {
        $type = isset($args['type']) ? $args['type'] : 'text_input';
        $cls = isset($args['class']) ? $args['class'] : 'widefat';
        $value = isset($args['value']) ? $args['value'] : '';
        $key = isset($args['key']) ? $args['key'] : false;

        if(!$key)
            $this->error(__('Set a key for this field', 'the-event'));
        elseif(method_exists($this, $type))
            $this->$type($value, $key, $cls, $args);
        else
            $this->error(__('Invalid field type', 'the-event'));
    }

    /**
     * Add a field.
     *
     * @since   1.0
     * @access  public
     * @param   string $key The field key
     * @param   array $args The field arguments.
     * @return  null
     */
    public function add_field($key, $args=array())
    {
        $args = wp_parse_args($args, array(
            'type'      => 'text_input', // the field type
            'class'     => 'widefat', // field class
            'value'     => '', // The value of the field
            'cleaners'  => array('esc_attr'), // Used in the `save` function
            'section'   => 'default', // the setting section.
            'page'      => $this->opt, // the settings page
            'label'     => '',
        ));

        $args['key'] = $key;

        $this->fields[$key] = $args;
    }

    /**
     * Add the settings fields. For use with the settings api.
     *
     * @since   1.0
     * @access  public
     * @return  null
     */
    public function add_settings_fields()
    {
        foreach($this->fields as $key => $args)
        {
            $n = $this->gen_nam($key);
            $args['label_for'] = $n;

            add_settings_field(
                $n,
                array($this, 'cb'),
                $args['page'],
                $args['section'],
                $args
            );
        }
    }

    /**
     * Render the fields.  For use outside the settings api.
     *
     * @since   1.0
     * @access  public
     * @return  null
     */
    public function render($id)
    {
        if(!is_null($this->meta))
            $this->setup_values($id);

        echo '<table class="form-table">';
        foreach($this->fields as $key => $field)
        {
            echo '<tr>';

            echo '<th scope="row">';
            $this->label($this->gen_name($key), $field['label']);
            echo '</th>';

            echo '<td>';
            $this->cb($field);
            echo '</td>';

            echo '</tr>';
        }
        echo '</table>';
    }

    /**
     * Clean and validate the data uses the `cleaners` specified for each
     * field.  Useful with the settings API.
     *
     * @since   1.0
     * @access  public
     * @param   array $dirty The data to validate/clean
     * @return  array
     */
    public function validate($dirty)
    {
        $clean = array();
        foreach($this->fields as $key => $field)
        {
            if(isset($dirty[$key]) && $dirty[$key])
            {
                $val = $dirty[$key];
                foreach($field['cleaners'] as $cb)
                    $val = call_user_func($cb, $val);
                $clean[$key] = $val;
            }
        }
        return $clean;
    }

    /**
     * Save the values using $this->meta.
     *
     * @since   1.0
     * @access  public
     * @param   array $values The values to save
     * @param   int $id The object ID
     * @return  null
     */
    public function save($id, $values)
    {
        $values = $this->validate($values);

        foreach($this->fields as $key => $field)
        {
            if(isset($values[$key]))
            {
                $this->meta->save($id, $key, $values[$key]);
            }
            else
            {
                // not in the validated array, assume it needs to be deleted
                $this->meta->delete($id, $key);
            }
        }
    }

    public function get_opt()
    {
        return $this->opt;
    }
} // end FieldFactory
