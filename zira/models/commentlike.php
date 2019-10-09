<?php
/**
 * Zira project.
 * commentlike.php
 * (c)2016 https://github.com/ziracms/zira
 */

namespace Zira\Models;

use Zira\Orm;

class Commentlike extends Orm {
    public static $table = 'comment_likes';
    public static $pk = 'id';
    public static $alias = 'cmtlke';

    public static function getTable() {
        return self::$table;
    }

    public static function getPk() {
        return self::$pk;
    }

    public static function getAlias() {
        return self::$alias;
    }

    public static function getReferences() {
        return array();
    }
    
    public static function removeCommentLikes($comment_id) {
        self::getCollection()
                ->delete()
                ->where('comment_id', '=', $comment_id)
                ->execute();
    }
}