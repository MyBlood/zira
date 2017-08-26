<?php
/**
 * Zira project.
 * category.php
 * (c)2016 http://dro1d.ru
 */

namespace Dash\Forms;

use Zira;
use Zira\Form;
use Zira\Locale;

class Category extends Form
{
    protected $_id = 'dash-category-form';

    protected $_label_class = 'col-sm-4 control-label';
    protected $_input_wrap_class = 'col-sm-8';
    protected $_input_offset_wrap_class = 'col-sm-offset-4 col-sm-8';

    public function __construct()
    {
        parent::__construct($this->_id);
    }

    protected function _init()
    {
        $this->setRenderPanel(false);
        $this->setFormClass('form-horizontal dash-window-form');
    }

    protected function _render()
    {
        $html = $this->open();
        $html .= $this->hidden('id');
        $html .= $this->hidden('root');
        $html .= $this->selectDropdown(Locale::t('Layout').'*','layout',Zira\View::getLayouts());
        $html .= $this->input(Locale::t('System name') . ' (' . Locale::t('URL') . ')*', 'name', array('placeholder'=>Locale::t('numbers and letters in lower case')));
        $html .= $this->input(Locale::t('Title') . '*', 'title');
        $html .= $this->checkbox(Locale::t('Enable comments'), 'comments_enabled', null, false);
        
        $html .= Zira\Helper::tag_open('div', array('id'=>'dashcategoryform_access_button'));
        $html .= Zira\Helper::tag_open('div', array('class'=>'form-group'));
        $html .= Zira\Helper::tag_open('div', array('class'=>'col-sm-offset-4 col-sm-8'));
        $html .= Zira\Helper::tag_open('div', array('class'=>'checkbox checkbox-float'));
        $html .= Zira\Helper::tag('span', null, array('class'=>'glyphicon glyphicon-menu-right', 'style'=>'float:left;top:3px'));
        $html .= Zira\Helper::tag_open('label', array('class' => 'col-sm-4 control-label', 'style'=>'width:auto;padding-top:0;padding-left:7px;', 'id'=>'dashcategoryform_access_label'));
        $html .= Locale::t('Restrict access');
        $html .= Zira\Helper::tag_close('label');
        $html .= Zira\Helper::tag_close('div');
        $html .= Zira\Helper::tag_close('div');
        $html .= Zira\Helper::tag_close('div');
        $html .= Zira\Helper::tag_close('div');
        $html .= Zira\Helper::tag_open('div', array('id'=>'dashcategoryform_access_container', 'style'=>'display:none'));
        $html .= $this->checkbox(Locale::t('Restrict access to category'), 'access_check', null, false);
        $html .= $this->checkbox(Locale::t('Restrict access to gallery'), 'gallery_check', null, false);
        $html .= $this->checkbox(Locale::t('Restrict access to files'), 'files_check', null, false);
        $html .= $this->checkbox(Locale::t('Restrict access to audio'), 'audio_check', null, false);
        $html .= $this->checkbox(Locale::t('Restrict access to video'), 'video_check', null, false);
        $html .= Zira\Helper::tag_close('div');
        
        $html .= $this->close();
        return $html;
    }

    protected function _validate()
    {
        $validator = $this->getValidator();
        $validator->registerCustom(array(get_class(), 'checkRoot'), 'root', Locale::t('An error occurred'));
        $validator->registerString('name', null, 255, true, Locale::t('Invalid value "%s"',Locale::t('System name')));
        $validator->registerRegexp('name', Zira\Models\Category::REGEXP_NAME, Locale::t('Invalid value "%s"',Locale::t('System name')));
        $validator->registerCustom(array(get_class(), 'checkName'), array('name','root'), Locale::t('Invalid value "%s"',Locale::t('System name')));
        $validator->registerCustom(array(get_class(), 'checkExists'), array('id','name','root'), Locale::t('Category with such name already exists'));
        $validator->registerString('title', 0, 255, true, Locale::t('Invalid value "%s"',Locale::t('Title')));
        $validator->registerUtf8('title', Locale::t('Invalid value "%s"',Locale::t('Title')));
        $validator->registerString('layout', 0, 0, true, Locale::t('Invalid value "%s"',Locale::t('Layout')));
        $validator->registerCustom(array(get_class(), 'checkLayout'), 'layout', Locale::t('Invalid value "%s"',Locale::t('Layout')));
    }

    public static function checkRoot($root) {
        $root = trim($root,'/');
        if (empty($root)) return true;
        $names = explode('/',$root);
        foreach($names as $name) {
            if (!preg_match(Zira\Models\Category::REGEXP_NAME, $name)) return false;
        }
        return true;
    }

    public static function checkName($name,$root) {
        $root = trim($root, '/');
        if (!empty($root)) $root .= '/';

        if ($name == 'dash' || in_array($name, Zira\Config::get('languages'))) return false;
        return Zira\Router::isRouteAvailable($root . $name);
    }

    public static function checkExists($id, $name,$root) {
        $root = trim($root,'/');
        if (!empty($root)) $root .= '/';

        $id = intval($id);
        $query = Zira\Models\Category::getCollection();
        $query->count();
        $query->where('name','=',$root.$name);
        if (!empty($id)) {
            $query->and_where('id','<>',$id);
        }
        return $query->get('co')==0;
    }

    public static function checkLayout($layout) {
        $layouts = Zira\View::getLayouts();
        return array_key_exists($layout, $layouts);
    }
}