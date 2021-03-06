<?php
/**
 * Zira project.
 * recordfiles.php
 * (c)2017 https://github.com/ziracms/zira
 */

namespace Dash\Windows;

use Zira;
use Zira\Permission;
use Dash\Windows\Files;

class Recordfiles extends Window {
    protected static $_icon_class = 'glyphicon glyphicon-file';
    protected static $_title = 'Files';

    public $item;

    public function init() {
        $this->setIconClass(self::$_icon_class);
        $this->setTitle(Zira\Locale::t(self::$_title));
        $this->setSidebarEnabled(false);
        $this->setViewSwitcherEnabled(false);
        $this->setBodyViewListVertical(true);

        $this->addDefaultMenuDropdownItem(
            $this->createMenuDropdownItem(Zira\Locale::t('Add file'), 'glyphicon glyphicon-plus-sign', 'desk_call(dash_recordfiles_add, this);', 'create')
        );
        $this->addDefaultMenuDropdownItem(
            $this->createMenuDropdownItem(Zira\Locale::t('Add URL'), 'glyphicon glyphicon-link', 'desk_call(dash_recordfiles_addurl, this);', 'create')
        );
        $this->addDefaultMenuDropdownItem(
            $this->createMenuDropdownItem(Zira\Locale::t('Description'), 'glyphicon glyphicon-list-alt', 'desk_window_edit_item(this);', 'edit', true)
        );

        $this->addDefaultContextMenuItem(
            $this->createContextMenuItem(Zira\Locale::t('Add file'), 'glyphicon glyphicon-plus-sign', 'desk_call(dash_recordfiles_add, this);', 'create')
        );
        $this->addDefaultContextMenuItem(
            $this->createContextMenuItem(Zira\Locale::t('Add URL'), 'glyphicon glyphicon-link', 'desk_call(dash_recordfiles_addurl, this);', 'create')
        );
        $this->addDefaultContextMenuItem(
            $this->createContextMenuItem(Zira\Locale::t('Description'), 'glyphicon glyphicon-list-alt', 'desk_window_edit_item(this);', 'edit', true)
        );

        $this->setDeleteActionEnabled(true);
    }

    public function create() {
        $this->addDefaultToolbarItem(
            $this->createToolbarButton(Zira\Locale::t('File'), Zira\Locale::t('Add file'), 'glyphicon glyphicon-plus-sign', 'desk_call(dash_recordfiles_add, this);', 'create')
        );
        $this->addDefaultToolbarItem(
            $this->createToolbarButton(Zira\Locale::t('URL'), Zira\Locale::t('Add URL'), 'glyphicon glyphicon-link', 'desk_call(dash_recordfiles_addurl, this);', 'create')
        );

        $this->setOnEditItemJSCallback(
            $this->createJSCallback(
                'desk_call(dash_recordfiles_desc, this);'
            )
        );

        $this->setOnDropJSCallback(
            $this->createJSCallback(
                'desk_call(dash_recordfiles_drop, this, element);'
            )
        );
        
        $this->addDefaultOnLoadScript(
            'desk_call(dash_recordfiles_load, this);'
        );

        $this->addStrings(array(
            'Enter description',
            'Enter URL'
        ));

        $this->includeJS('dash/recordfiles');
    }

    public function load() {
        if (empty($this->item) || !is_numeric($this->item)) {
            return array('error'=>Zira\Locale::t('An error occurred'));
        }
        if (!Permission::check(Permission::TO_CREATE_RECORDS) || 
            !Permission::check(Permission::TO_EDIT_RECORDS)
        ) {
            return array('error'=>Zira\Locale::t('Permission denied'));
        }

        $record = new Zira\Models\Record($this->item);
        if (!$record->loaded()) {
            return array('error'=>Zira\Locale::t('An error occurred'));
        }
        $this->setTitle(Zira\Locale::t(self::$_title) .' - ' . $record->title);

        $files = Zira\Models\File::getCollection()
                            ->where('record_id','=',$record->id)
                            ->order_by('id', 'asc')
                            ->get();

        $items = array();
        foreach($files as $file) {
            if (!empty($file->path)) {
                $real_path = str_replace('/', DIRECTORY_SEPARATOR, $file->path);
                $name = Zira\Helper::basename($file->path);
                if ($file->download_count>0) {
                    $name .= '&nbsp;&nbsp;&nbsp;('.Zira\Locale::t('%s downloads', $file->download_count).')';
                }
            } else {
                $name = $file->url;
                $real_path = null;
            }
            $inactive = isset($real_path) && !file_exists($real_path) ? 1 : 0;
            if (isset($real_path) && !$inactive && ($size=Files::image_size(ROOT_DIR . DIRECTORY_SEPARATOR . $real_path))!=false) {
                $items[]=$this->createBodyItem(Zira\Helper::html($name), Zira\Helper::html($file->description), Zira\Helper::urlencode(Zira\Helper::baseUrl($file->path)), $file->id, null, false, array('type'=>'image', 'description'=>$file->description, 'inactive'=>$inactive));
            } else if (Files::is_audio($name)) {
                $items[]=$this->createBodyAudioItem(Zira\Helper::html($name), Zira\Helper::html($file->description), $file->id, null, false, array('type'=>'audio', 'description'=>$file->description, 'inactive'=>$inactive));
            } else if (Files::is_video($name)) {
                $items[]=$this->createBodyVideoItem(Zira\Helper::html($name), Zira\Helper::html($file->description), $file->id, null, false, array('type'=>'video', 'description'=>$file->description, 'inactive'=>$inactive));
            } else if (Files::is_archive($name)) {
                $items[]=$this->createBodyArchiveItem(Zira\Helper::html($name), Zira\Helper::html($file->description), $file->id, null, false, array('type'=>'archive', 'description'=>$file->description, 'inactive'=>$inactive));
            } else if (Files::is_txt($name)) {
                $items[]=$this->createBodyFileItem(Zira\Helper::html($name), Zira\Helper::html($file->description), $file->id, null, false, array('type'=>'txt', 'description'=>$file->description, 'inactive'=>$inactive));
            } else if (Files::is_html($name)) {
                $items[]=$this->createBodyFileItem(Zira\Helper::html($name), Zira\Helper::html($file->description), $file->id, null, false, array('type'=>'html', 'description'=>$file->description, 'inactive'=>$inactive));
            } else {
                $items[]=$this->createBodyFileItem(Zira\Helper::html($name), Zira\Helper::html($file->description), $file->id, null, false, array('type'=>'file', 'description'=>$file->description, 'inactive'=>$inactive));
            }
        }

        $this->setBodyItems($items);

        $this->setData(array(
            'items' => array($this->item)
        ));
    }
}