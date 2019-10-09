<?php
/**
 * Zira project.
 * commentsettings.php
 * (c)2016 https://github.com/ziracms/zira
 */

namespace Dash\Models;

use Zira;
use Zira\Permission;

class Commentsettings extends Model {
    public function save($data) {
        if (!Permission::check(Permission::TO_CHANGE_OPTIONS)) {
            return array('error' => Zira\Locale::t('Permission denied'));
        }

        $form = new \Dash\Forms\Commentsettings();
        if ($form->isValid()) {
            $options = array(
                'comments_max_nesting'=>'int',
                'comments_limit'=>'int',
                'comment_moderate'=>'int',
                'comment_anonymous'=>'int',
                'comment_notify_email'=>'string',
                'comments_allowed'=>'int',
                'comments_captcha'=>'int',
                'comments_captcha_users'=>'int',
                'comment_min_chars'=>'int'
            );

            $config_ids = array();
            $user_configs = Zira\Models\Option::getCollection()->get();
            foreach($user_configs as $user_config) {
                $config_ids[$user_config->name] = $user_config->id;
            }

            foreach($options as $option=>$type) {
                if (!array_key_exists($option, $config_ids)) {
                    $optionObj = new Zira\Models\Option();
                } else {
                    $optionObj = new Zira\Models\Option($config_ids[$option]);
                }
                $optionObj->name = $option;
                $value = $form->getValue($option);

                if ($type=='int') $value=intval($value);

                $optionObj->value = $value;
                $optionObj->module = 'zira';
                $optionObj->save();
            }

            Zira\Models\Option::raiseVersion();

            return array('message'=>Zira\Locale::t('Successfully saved'));
        } else {
            return array('error'=>$form->getError());
        }
    }
}