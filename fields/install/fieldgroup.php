<?php
/**
 * Zira project.
 * fieldgroup.php
 * (c)2018 https://github.com/ziracms/zira
 */

namespace Fields\Install;

use Zira\Db\Table;
use Zira\Db\Field;

class Fieldgroup extends Table {
    protected $_table = 'field_groups';

    public function __construct() {
        parent::__construct($this->_table);
    }

    public function getFields() {
        return array(
            'id' => Field::primary(),
            'title' => Field::string(true),
            'description' => Field::string(false),
            'placeholder' => Field::string(true),
            'category_id' => Field::int(true, true),
            'language' => Field::string(false),
            'sort_order' => Field::int(true, false, 0),
            'active' => Field::int(true, true, 0),
            'tpl' => Field::string(false)
        );
    }

    public function getKeys() {
        return array(
            'search' => array('active', 'category_id', 'language')
        );
    }

    public function getUnique() {
        return array(

        );
    }

    public function getDefaults() {
        return array(

        );
    }
}