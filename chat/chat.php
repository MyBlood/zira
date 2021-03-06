<?php
/**
 * Zira project.
 * chat.php
 * (c)2017 https://github.com/ziracms/zira
 */

namespace Chat;

use Zira;
use Dash;

class Chat {
    const PERMISSION_MODERATE = 'Moderate chat';
    const WIDGET_CLASS = '\Chat\Widgets\Chat';
    const WIDGET_PLACEHOLDER = Zira\View::VAR_SIDEBAR_RIGHT;
    const DEFAULT_DELAY = 5;
    const MAX_CHARS = 1024;
    const WIDGET_LIMIT = 30;
    
    const TRASH_TIME = 2592000; // 30 days
    
    private static $_instance;

    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function onActivate() {
        Zira\Assets::registerCSSAsset('chat/chat.css');
        Zira\Assets::registerJSAsset('chat/chat.js');
    }

    public function onDeactivate() {
        Zira\Assets::unregisterCSSAsset('chat/chat.css');
        Zira\Assets::unregisterJSAsset('chat/chat.js');
    }

    public function beforeDispatch() {
        Zira\Assets::registerCSSAsset('chat/chat.css');
        Zira\Assets::registerJSAsset('chat/chat.js');
    }

    public function bootstrap() {
        Zira\View::addDefaultAssets();
        Zira\View::addStyle('chat/chat.css');
        Zira\View::addScript('chat/chat.js');
        Zira\View::addParser();
        
        if (ENABLE_CONFIG_DATABASE && Dash\Dash::getInstance()->isPanelEnabled() && Zira\Permission::check(Zira\Permission::TO_ACCESS_DASHBOARD) && (Zira\Permission::check(Zira\Permission::TO_CHANGE_OPTIONS) || Zira\Permission::check(self::PERMISSION_MODERATE))) {
            Dash\Dash::loadDashLanguage();
            Dash\Dash::getInstance()->addPanelModulesGroupItem('glyphicon glyphicon-transfer', Zira\Locale::tm('Chat', 'chat', null, Dash\Dash::getDashLanguage()), null, 'chatsWindow()');
            Dash\Dash::getInstance()->registerModuleWindowClass('chatsWindow', 'Chat\Windows\Chats', 'Chat\Models\Chats');
            Dash\Dash::getInstance()->registerModuleWindowClass('chatWindow', 'Chat\Windows\Chat', 'Chat\Models\Chats');
            Dash\Dash::getInstance()->registerModuleWindowClass('chatMessagesWindow', 'Chat\Windows\Messages', 'Chat\Models\Messages');
            Dash\Dash::getInstance()->registerModuleWindowClass('chatMessageWindow', 'Chat\Windows\Message', 'Chat\Models\Messages');
            Dash\Dash::getInstance()->registerModuleWindowClass('chatSettingsWindow', 'Chat\Windows\Settings', 'Chat\Models\Settings');
            Dash\Dash::unloadDashLanguage();
        }
    }
}