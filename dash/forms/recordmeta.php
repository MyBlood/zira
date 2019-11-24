<?php
/**
 * Zira project.
 * recordmeta.php
 * (c)2016 https://github.com/ziracms/zira
 */

namespace Dash\Forms;

use Zira;
use Zira\Form;
use Zira\Locale;

class Recordmeta extends Form
{
    protected $_id = 'dash-recordmeta-form';

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
        $html .= $this->input(Locale::t('Window title'), 'meta_title');
        $html .= $this->input(Locale::t('Keywords'), 'meta_keywords');
        $html .= $this->textarea(Locale::t('Description'), 'meta_description');

        $html .= $this->hidden('record_tags', array('class'=>'dash_form_record_tags_hidden'));
        $html .= Zira\Helper::tag_open('div', array('class'=>'dash_form_record_tags_wrapper', 'style'=>'position:relative'));
        $html .= $this->input(Locale::t('Tags'),'tags_input',array('class'=>'form-control dash_form_record_tags_input'));
        $html .= Zira\Helper::tag('span', null, array('class'=>'glyphicon glyphicon-plus-sign add', 'style'=>'position:absolute;top:24px;right:20px;cursor:pointer'));
        $html .= Zira\Helper::tag_open('div', array('class'=>'form_group'));
        $html .= Zira\Helper::tag('div', null, array('class'=>'col-sm-4'));
        $html .= Zira\Helper::tag('div', null, array('class'=>'col-sm-8 dash_form_record_tags_container'));
        $html .= Zira\Helper::tag_close('div');
        $html .= Zira\Helper::tag_close('div');
        
        $html .= $this->close();
        return $html;
    }

    protected function _validate()
    {
        $validator = $this->getValidator();
        $validator->registerString('meta_title', null, 255, false, Locale::t('Invalid value "%s"',Locale::t('Window title')));
        $validator->registerString('meta_keywords', null, 255, false, Locale::t('Invalid value "%s"',Locale::t('Keywords')));
        $validator->registerNoTags('meta_title', Locale::t('Invalid value "%s"',Locale::t('Window title')));
        $validator->registerUtf8('meta_title', Locale::t('Invalid value "%s"',Locale::t('Window title')));
        $validator->registerNoTags('meta_keywords', Locale::t('Invalid value "%s"',Locale::t('Keywords')));
        $validator->registerUtf8('meta_keywords', Locale::t('Invalid value "%s"',Locale::t('Keywords')));
        $validator->registerNoTags('meta_description', Locale::t('Invalid value "%s"',Locale::t('Description')));
        $validator->registerUtf8('meta_description', Locale::t('Invalid value "%s"',Locale::t('Description')));
    }
}