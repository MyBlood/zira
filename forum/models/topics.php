<?php
/**
 * Zira project.
 * topics.php
 * (c)2016 http://dro1d.ru
 */

namespace Forum\Models;

use Zira;
use Dash;
use Forum;
use Zira\Permission;

class Topics extends Dash\Models\Model {
    public function save($data) {
        if (!Permission::check(Forum\Forum::PERMISSION_MODERATE)) {
            return array('error' => Zira\Locale::t('Permission denied'));
        }

        $form = new Forum\Forms\Topic();
        if ($form->isValid()) {
            $id = (int)$form->getValue('id');
            if ($id) {
                $thread = new Forum\Models\Topic($id);
                if (!$thread->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

                if ($thread->forum_id != $form->getValue('forum_id')) {
                    $forum_old = new \Forum\Models\Forum($thread->forum_id);
                    if (!$forum_old->loaded()) return array('error' => Zira\Locale::t('An error occurred'));
                    $forum_new = new \Forum\Models\Forum($form->getValue('forum_id'));
                    if (!$forum_new->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

                    if ($thread->published == Forum\Models\Topic::STATUS_PUBLISHED) {
                        if ($forum_old->last_user_id == $thread->creator_id) {
                            $forum_old->last_user_id = null;
                        }
                        $forum_old->topics--;
                        if ($forum_old->topics < 0) $forum_old->topics = 0;
                        $forum_old->save();

                        $forum_new->topics++;
                        $forum_new->save();
                    }

                    \Forum\Models\Search::clearTopicIndex($thread);
                    $thread->forum_id = (int)$form->getValue('forum_id');
                }
            } else {
                $forum = new \Forum\Models\Forum($form->getValue('forum_id'));
                if (!$forum->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

                $thread = new Forum\Models\Topic();
                $thread->category_id = $forum->category_id;
                $thread->forum_id = $forum->id;
                $thread->creator_id = Zira\User::getCurrent()->id;
                $thread->date_created = date('Y-m-d H:i:s');
                $thread->published = Forum\Models\Topic::STATUS_NOT_PUBLISHED;

                if ($thread->published == Forum\Models\Topic::STATUS_PUBLISHED) {
                    $forum->topics++;
                    $forum->save();
                }
            }

            $thread->title = $form->getValue('title');
            $description = $form->getValue('description');
            $thread->description = !empty($description) ? $description : null;
            $meta_title = $form->getValue('meta_title');
            $thread->meta_title = !empty($meta_title) ? $meta_title : null;
            $meta_description = $form->getValue('meta_description');
            $thread->meta_description = !empty($meta_description) ? $meta_description : null;
            $meta_keywords = $form->getValue('meta_keywords');
            $thread->meta_keywords = !empty($meta_keywords) ? $meta_keywords : null;
            $info = $form->getValue('info');
            $thread->info = !empty($info) ? $info : null;
            $thread->status = (int)$form->getValue('status');
            $thread->active = (int)$form->getValue('active') ? 1 : 0;
            $thread->sticky = (int)$form->getValue('sticky') ? 1 : 0;
            $thread->date_modified = date('Y-m-d H:i:s');

            $language = $form->getValue('language');
            if (empty($language)) $language = null;
            $thread->language = $language;

            $thread->save();

            if ($thread->published == Forum\Models\Topic::STATUS_PUBLISHED) {
                \Forum\Models\Search::indexTopic($thread);
            }

            return array('message'=>Zira\Locale::t('Successfully saved'), 'close'=>true);
        } else {
            return array('error'=>$form->getError());
        }
    }

    public function delete($data) {
        if (empty($data) || !is_array($data)) return array('error' => Zira\Locale::t('An error occurred'));
        if (!Permission::check(Forum\Forum::PERMISSION_MODERATE)) {
            return array('error'=>Zira\Locale::t('Permission denied'));
        }

        foreach($data as $topic_id) {
            Forum\Models\Topic::deleteTopic($topic_id);
        }

        return array('reload' => $this->getJSClassName());
    }

    public function close($id) {
        if (empty($id)) return array('error' => Zira\Locale::t('An error occurred'));
        if (!Permission::check(Forum\Forum::PERMISSION_MODERATE)) {
            return array('error'=>Zira\Locale::t('Permission denied'));
        }

        $topic = new Forum\Models\Topic($id);
        if (!$topic->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

        $topic->active = 0;
        $topic->save();

        return array('reload' => $this->getJSClassName());
    }

    public function stick($id) {
        if (empty($id)) return array('error' => Zira\Locale::t('An error occurred'));
        if (!Permission::check(Forum\Forum::PERMISSION_MODERATE)) {
            return array('error'=>Zira\Locale::t('Permission denied'));
        }

        $topic = new Forum\Models\Topic($id);
        if (!$topic->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

        $topic->sticky = 1;
        $topic->save();

        return array('reload' => $this->getJSClassName());
    }

    public function activate($id) {
        if (empty($id)) return array('error' => Zira\Locale::t('An error occurred'));
        if (!Permission::check(Forum\Forum::PERMISSION_MODERATE)) {
            return array('error'=>Zira\Locale::t('Permission denied'));
        }

        $topic = new Forum\Models\Topic($id);
        if (!$topic->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

        $forum = new Forum\Models\Forum($topic->forum_id);
        if (!$forum->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

        $user = new Zira\Models\User($topic->creator_id);
        if (!$user->loaded()) return array('error' => Zira\Locale::t('An error occurred'));

        $topic->published = Forum\Models\Topic::STATUS_PUBLISHED;
        $topic->save();

        Forum\Models\Forum::getCollection()
                ->update(array(
                    'date_modified' => date('Y-m-d H:i:s'),
                    'last_user_id' => $user->id,
                    'topics' => ++$forum->topics
                ))->where('id', '=', $forum->id)
                ->execute();

        $messages = Forum\Models\Message::getCollection()
                                ->where('topic_id','=',$topic->id)
                                ->get(null, true);

        foreach($messages as $message) {
            if ($message['published'] == Forum\Models\Message::STATUS_PUBLISHED) continue;
            if ($message['creator_id'] != $user->id) continue;

            $messageObj = new Forum\Models\Message();
            $messageObj->loadFromArray($message);
            $messageObj->published = Forum\Models\Message::STATUS_PUBLISHED;
            $messageObj->save();

            Topic::getCollection()
                ->update(array(
                    'date_modified' => date('Y-m-d H:i:s'),
                    'last_user_id' => $user->id,
                    'messages' => ++$topic->messages
                ))->where('id', '=', $topic->id)
                ->execute();

            $user->posts++;
            $user->save();
        }

        \Forum\Models\Search::indexTopic($topic);

        return array('reload' => $this->getJSClassName());
    }

    public function info($topic_id) {
        if (empty($topic_id)) return array('error' => Zira\Locale::t('An error occurred'));
        if (!Permission::check(Forum\Forum::PERMISSION_MODERATE)) {
            return array();
        }
        $topic = new Forum\Models\Topic($topic_id);
        if (!$topic->loaded()) return array('error' => Zira\Locale::t('An error occurred'));
        $forum = new Forum\Models\Forum($topic->forum_id);
        if (!$forum->loaded()) return array('error' => Zira\Locale::t('An error occurred'));
        $user = new Zira\Models\User($topic->creator_id);
        if (!$user->loaded()) $user = null;

        $info = array();

        $info[]='<span class="glyphicon glyphicon-th-list" title="'.Zira\Locale::tm('Forum','forum').'"></span> '.Zira\Helper::html($forum->title);
        if ($user) {
            $info[] = '<span class="glyphicon glyphicon-user" title="' . Zira\Locale::tm('Topic starter','forum') . '"></span> ' . Zira\Helper::html($user->username);
        }
        $info[]='<span class="glyphicon glyphicon-calendar" title="'.Zira\Locale::tm('Creation date','forum').'"></span> '.date(Zira\Config::get('date_format'), strtotime($topic->date_created));

        return $info;
    }
}