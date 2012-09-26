<?php
/**
 * A wrapper around the meta data api to make prefixing and such easier.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     TheEvent
 */

namespace PMG\TheEvent;

!defined('ABSPATH') && exit;

class Meta
{
    private static $prefix = '';

    private static $registry = array();

    private $type;

    protected function __construct($type)
    {
        $this->type = $type;
    }

    public static function instance($type)
    {
        if(isset(self::$registry[$type]))
            return self::$registry[$type];

        self::$registry[$type] = new self($type);

        return self::$registry[$type];
    }

    public static function set_prefix($prefix)
    {
        self::$prefix = $prefix;
    }

    protected function get_key($k)
    {
        return "_" . static::$prefix . "_{$k}";
    }

    public function save($id, $key, $val)
    {
        $old = $this->get($id, $key);

        return update_metadata(
            $this->type,
            $id,
            $this->get_key($key),
            $val,
            $old
        );
    }

    public function get($id, $key, $default='')
    {
        $v = get_metadata(
            $this->type,
            $id,
            $this->get_key($key),
            true
        );

        return $v ? $v : $default;
    }

    public function delete($id, $key, $val='')
    {
        return delete_metadata(
            $this->type,
            $id,
            $this->get_key($key),
            $val
        );
    }

    public function delete_all($id, $key, $val='')
    {
        return delete_metadata(
            $this->type,
            $id,
            $this->get_key($key),
            $val,
            true
        );
    }
} // end MetaFactory
