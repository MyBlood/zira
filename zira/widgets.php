<?php
/**
 * Zira project.
 * widgets.php
 * (c)2016 https://github.com/ziracms/zira
 */

namespace Zira;

class Widgets {
    const CACHE_KEY = 'widgets';

    public static function getDefaultDbWidgets() {
        if (Zira::isOnline()) {
            return array(
                'logo' => '\Zira\Widgets\Logo',
                'topmenu' => '\Zira\Widgets\Topmenu',
                'childmenu' => '\Zira\Widgets\Childmenu',
                'footermenu' => '\Zira\Widgets\Footermenu',
                'languages' => '\Zira\Widgets\Languages',
                'usermenu' => '\Zira\Widgets\Usermenu',
                'usermenupopup' => '\Zira\Widgets\Usermenupopup'
            );
        } else {
            return array(
                'logo' => '\Zira\Widgets\Logo',
                'languages' => '\Zira\Widgets\Languages',
                'usermenu' => '\Zira\Widgets\Usermenu',
                'usermenupopup' => '\Zira\Widgets\Usermenupopup'
            );
        }
    }

    public static function addDefaultDbWidgets() {
        $defaultDbWidgets = self::getDefaultDbWidgets();

        if (count(Config::get('languages'))==1) {
            unset($defaultDbWidgets['languages']);
        }
        if (!Config::get('user_signup_allow') && !User::isAuthorized()) {
            unset($defaultDbWidgets['usermenu']);
        }
        unset($defaultDbWidgets['usermenupopup']);
        
        foreach($defaultDbWidgets as $name=>$class) {
            View::addWidget($class);
        }
    }

    public static function load($category_id=null) {
        if (!Config::get('db_widgets_enabled', true)) {
            self::addDefaultDbWidgets();
            return;
        }
        if (CACHE_WIDGETS_LIST && Config::get('db_widgets_count',0)<=1000) {
            $rows = self::loadAll($category_id);
        } else {
            $rows = self::loadPartial($category_id);
        }

        $modules = array_merge(array('zira'),Config::get('modules'));
        foreach ($rows as $row) {
            if (!in_array($row->module, $modules)) continue;
            View::addDbWidget($row, $row->placeholder);
        }
    }

    protected static function loadPartial($category_id) {
        if ($category_id===null) {
            $rows = Models\Widget::getCollection()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->merge()
                    ->order_by('sort_order','asc')
                    ->get();
        } else if (!is_array($category_id)) {
            $rows = Models\Widget::getCollection()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','=',$category_id)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','=',$category_id)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','=',$category_id)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','=',$category_id)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->merge()
                    ->order_by('sort_order','asc')
                    ->get();
        } else {
            $query = Models\Widget::getCollection()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id','is',null)
                    ->and_where('active','=',Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ;

            foreach($category_id as $_category_id) {
                $query->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id', '=', $_category_id)
                    ->and_where('active', '=', Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id', '=', $_category_id)
                    ->and_where('active', '=', Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', 'is', null)
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','is',null)
                    ->and_where('category_id', '=', $_category_id)
                    ->and_where('active', '=', Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ->union()
                    ->open_query()
                    ->where('language','=',Locale::getLanguage())
                    ->and_where('category_id', '=', $_category_id)
                    ->and_where('active', '=', Models\Widget::STATUS_ACTIVE)
                    ->and_where('url', '=', Router::getRequest())
                    ->order_by('sort_order','asc')
                    ->close_query()
                    ;
            }

            $rows = $query->merge()
                            ->order_by('sort_order', 'asc')
                            ->get();
        }

        return $rows;
    }

    protected static function loadAll($category_id) {
        $cache_key = self::CACHE_KEY . '.' . Locale::getLanguage();
        $rows=Cache::getArray($cache_key);
        if ($rows===false) {
            $rows = Models\Widget::getCollection()
                ->open_query()
                ->where('language','is',null)
                ->order_by('sort_order', 'asc')
                ->close_query()
                ->union()
                ->open_query()
                ->where('language','=',Locale::getLanguage())
                ->order_by('sort_order', 'asc')
                ->close_query()
                ->merge()
                ->order_by('sort_order', 'asc')
                ->get();
            Cache::setArray($cache_key, $rows);
        }

        $_rows = array();
        foreach($rows as $row) {
            if ($row->active!=Models\Widget::STATUS_ACTIVE) continue;
            if ($row->category_id!==null) {
                if (!is_array($category_id) && $row->category_id!=$category_id) continue;
                else if (is_array($category_id) && !in_array($row->category_id,$category_id)) continue;
            }
            $_rows []= $row;
        }

        return $_rows;
    }
}