<?php
/**
 * Zira project.
 * index.php
 * (c)2016 https://github.com/ziracms/zira
 */

namespace Zira\Content;

use Zira;

class Index extends Zira\Page {
    public static function content() {
        static::setRedirectUrl('');
        
        $limit = Zira\Config::get('home_records_limit');
        if (!$limit) $limit = Zira\Config::get('records_limit', 10);
        
        $order = Zira\Page::getHomeRecordsOrderColumn();
        $use_pagination = Zira\Config::get('enable_pagination');
        
        if ($use_pagination) {
            $page = (int)Zira\Request::get('page');
        } else {
            $page = 1;
        }
        $top_records_count = 0;
        
        $record = static::record();
        $categories = static::categories($limit, $order, $page, $use_pagination, $top_records_count);
        
        if ($use_pagination && $top_records_count > 0) {
            $pagination = new Zira\Pagination();
            $pagination->setLimit($limit);
            $pagination->setPage($page);
            $pagination->setTotal($top_records_count);
        } else {
            $pagination = null;
        }

        if (!empty($categories)) {
            $layout = Zira\Config::get('layout');
            if (static::getLayout()!==null) $layout = static::getLayout();
            Zira\View::addPlaceholderView(Zira\View::VAR_CONTENT, array(
                //'grid' => $layout != Zira\View::LAYOUT_ALL_SIDEBARS,
                'grid' => Zira\Config::get('home_site_records_grid', Zira\Config::get('site_records_grid', 1)),
                'categories' => $categories,
                'pagination' => $pagination
            ), 'zira/home');
        }

        // adding meta tags
        $title = Zira\Config::get('home_title');
        $meta_title = Zira\Config::get('home_window_title');
        $meta_keywords = Zira\Config::get('home_keywords');
        $meta_description = Zira\Config::get('home_description');
        $thumb = null;
        if ($record) {
            if (!$title) $title = $record->title;
            if (!$meta_title) {
                if ($record->meta_title) $meta_title = $record->meta_title;
                else $meta_title = $record->title;
            }
            if (!$meta_description) {
                if ($record->meta_description) $meta_description = $record->meta_description;
                else $meta_description = $record->description;
            }
            if (!$meta_keywords) $meta_keywords = $record->meta_keywords;
            if ($record->thumb) $thumb = $record->thumb;
        } else {
            //if (!$title) $title = Zira\Config::get('site_name');
            if (!$meta_title) $meta_title = Zira\Config::get('site_title');
            if (!$meta_keywords) $meta_keywords = Zira\Config::get('site_keywords');
            if (!$meta_description) $meta_description = Zira\Config::get('site_description');
        }

        static::setTitle(Zira\Locale::t($meta_title));
        static::setKeywords(Zira\Locale::t($meta_keywords));
        static::setDescription(Zira\Locale::t($meta_description));
        static::addOpenGraphTags(Zira\Locale::t($meta_title), Zira\Locale::t($meta_description), '', $thumb);

        //Zira\View::setRenderBreadcrumbs(false);

        $data = array(
            static::VIEW_PLACEHOLDER_TITLE => Zira\Locale::t($title)
        );

        $admin_icons = null;

        if ($record) {
            $data[static::VIEW_PLACEHOLDER_IMAGE] = $record->image;
            $data[static::VIEW_PLACEHOLDER_CONTENT] = $record->content;
            $data[static::VIEW_PLACEHOLDER_CLASS] = 'parse-content';
            Zira\View::addParser();

            if (!static::allowPreview() && Zira\Permission::check(Zira\Permission::TO_ACCESS_DASHBOARD) && Zira\Permission::check(Zira\Permission::TO_VIEW_RECORDS) && Zira\Permission::check(Zira\Permission::TO_EDIT_RECORDS)) {
                $admin_icons = Zira\Helper::tag_open('div', array('class'=>'editor-links-wrapper'));
                $admin_icons .= Zira\Helper::tag('span', null, array('class'=>'glyphicon glyphicon-file record', 'data-item'=>$record->id));
                $admin_icons .= Zira\Helper::tag_close('div');
            }

            $data[static::VIEW_PLACEHOLDER_ADMIN_ICONS] = $admin_icons;
        } else {
            $data[static::VIEW_PLACEHOLDER_DESCRIPTION] = Zira\Locale::t($meta_description);
        }

        parent::render($data);
    }

    public static function record() {
        $record = null;
        $record_name = Zira\Config::get('home_record_name');
        if (!empty($record_name)) {
            $record = Zira\Models\Record::getCollection()
                        ->select(Zira\Models\Record::getFields())
                        ->where('category_id', '=', Zira\Category::ROOT_CATEGORY_ID)
                        ->and_where('language', '=', Zira\Locale::getLanguage())
                        ->and_where('name', '=', $record_name)
                        ->and_where('published', '=', Zira\Models\Record::STATUS_PUBLISHED)
                        ->get(0)
                        ;

            if ($record) {
                $slider_enabled = Zira\Config::get('slider_enabled', 1);
                $gallery_enabled = Zira\Config::get('gallery_enabled', 1);
                $files_enabled = Zira\Config::get('files_enabled', 1);
                $audio_enabled = Zira\Config::get('audio_enabled', 1);
                $video_enabled = Zira\Config::get('video_enabled', 1);
                
                if (!$record->slides_count) $slider_enabled = false;
                if (!$record->images_count) $gallery_enabled = false;
                if (!$record->files_count) $files_enabled = false;
                if (!$record->audio_count) $audio_enabled = false;
                if (!$record->video_count) $video_enabled = false;

                if (!$record->access_check || Zira\Permission::check(Zira\Permission::TO_VIEW_RECORD)) {
                    static::setRecordId($record->id);
                    static::setRecordUrl(static::generateRecordUrl(null, $record->name));

                    // checking permission for gallery, files, audio & video
                    if (($record->gallery_check || Zira\Config::get('gallery_check')) &&
                       !Zira\Permission::check(Zira\Permission::TO_VIEW_GALLERY)
                    ) {
                        $access_gallery = false;
                    } else {
                        $access_gallery = true;
                    }
                    if (($record->files_check || Zira\Config::get('files_check')) && 
                        !Zira\Permission::check(Zira\Permission::TO_DOWNLOAD_FILES)
                    ) {
                        $access_files = false;
                    } else {
                        $access_files = true;
                    }
                    if (($record->audio_check || Zira\Config::get('audio_check')) && 
                       !Zira\Permission::check(Zira\Permission::TO_LISTEN_AUDIO)
                    ) {
                        $access_audio = false;
                    } else {
                        $access_audio = true;
                    }
                    if (($record->video_check || Zira\Config::get('video_check')) && 
                       !Zira\Permission::check(Zira\Permission::TO_VIEW_VIDEO)
                    ) {
                        $access_video = false;
                    } else {
                        $access_video = true;
                    }
                    
                    if ($slider_enabled) {
                        $slides = static::getRecordSlides($record->id);
                        $slides_co = count($slides);
                    } else {
                        $slides = array();
                        $slides_co = 0;
                    }

                    $images_limit = intval(Zira\Config::get('gallery_limit', 0));
                    if ($gallery_enabled && $access_gallery) {
                        $images_co = static::getRecordImagesCount($record->id);
                        if ($images_co>0) {
                            $images = static::getRecordImages($record->id, $images_limit);
                        } else {
                            $images = array();
                        }
                    } else if ($gallery_enabled && !$access_gallery) {
                        $images = array();
                        $images_co = static::getRecordImagesCount($record->id);
                    } else {
                        $images = array();
                        $images_co = 0;
                    }
                    
                    if ($files_enabled && $access_files) {
                        $files = static::getRecordFiles($record->id);
                        $files_co = count($files);
                    } else if ($files_enabled && !$access_files) {
                        $files = array();
                        $files_co = static::getRecordFilesCount($record->id);
                    } else {
                        $files = array();
                        $files_co = 0;
                    }
                    
                    if ($audio_enabled && $access_audio) {
                        $audio = static::getRecordAudio($record->id);
                        $audio_co = count($audio);
                    } else if ($audio_enabled && !$access_audio) {
                        $audio = array();
                        $audio_co = static::getRecordAudioCount($record->id);
                    } else {
                        $audio = array();
                        $audio_co = 0;
                    }
                    
                    if ($video_enabled && $access_video) {
                        $video = static::getRecordVideos($record->id);
                        $video_co = count($video);
                    } else if ($video_enabled && !$access_video) {
                        $video = array();
                        $video_co = static::getRecordVideosCount($record->id);
                    } else {
                        $video = array();
                        $video_co = 0;
                    }

                    if ($slides_co > 0) static::setSlider($slides, true);
                    if ($images_co > 0) static::setGallery($images, $access_gallery, $images_limit, $images_co, $record->id);
                    if ($audio_co > 0) static::setAudio($audio, $access_audio);
                    if ($video_co > 0) static::setVideo($video, $access_video, $record->image);
                    if ($files_co > 0) static::setFiles($files, $access_files);

                    if ((!empty($slides) && $slider_enabled) || (!empty($video) && $video_enabled))
                        $record->image = null;
                } else {
                    $record = null;
                }
            }
        }
        return $record;
    }

    public static function categories($limit, $order, &$page, $use_pagination, &$top_records_count) {
        $use_cache = !$use_pagination || $page<=1;
        $categories = array();
        if (Zira\Config::get('home_records_enabled', true)) {
            $categories_cache_key = 'home.categories.'.Zira\Locale::getLanguage();
            if ($use_cache) {
                $categories_cache_key = 'home.categories.'.Zira\Locale::getLanguage();
                $cached_categories = Zira\Cache::getArray($categories_cache_key);
            } else {
                $cached_categories = false;
            }
            if ($cached_categories!==false) {
                $categories = $cached_categories;
                foreach ($categories as $category) {
                    if (empty($category['category_id']) && !empty($category['count'])) {
                        $top_records_count = $category['count'];
                    }
                    if (isset($category['records'])) {
                        static::runRecordsHook($category['records']);
                    }
                }
            } else {
                // root category records
                $pages = 1;
                if ($use_pagination) {
                    $top_records_count = self::getCategoryRecordsCount(Zira\Category::ROOT_CATEGORY_ID, true);
                    $pages = ceil($top_records_count / $limit);
                }
                if ($page > $pages) $page = $pages;
                if ($page < 1) $page = 1;
                $offset = $limit * ($page - 1);
                
                $records_q = Zira\Models\Record::getCollection()
                    ->select('id', 'name', 'author_id', 'title', 'description', 'image', 'thumb', 'creation_date', 'rating', 'comments')
                    ->join(Zira\Models\User::getClass(), array('author_username' => 'username', 'author_firstname' => 'firstname', 'author_secondname' => 'secondname'))
                    ->where('category_id', '=', Zira\Category::ROOT_CATEGORY_ID)
                    ->and_where('language', '=', Zira\Locale::getLanguage())
                    ->and_where('published', '=', Zira\Models\Record::STATUS_PUBLISHED)
                    ->and_where('front_page', '=', Zira\Models\Record::STATUS_FRONT_PAGE)
                    ;
                
                $records_q->order_by($order, 'desc');
                if ($order!='id') {
                    $records_q->order_by('id', 'desc');
                }
                $records_q->limit($limit, $offset);
                
                $records = $records_q->get();

                if ($records) {
                    for ($i = 0; $i < count($records); $i++) {
                        $records[$i]->category_name = '';
                        $records[$i]->category_title = '';
                    }
                    $categories [] = array(
                        'category_id' => Zira\Category::ROOT_CATEGORY_ID,
                        'title' => '',
                        'url' => '',
                        'records' => $records,
                        'count' => $top_records_count,
                        'settings' => array(
                            'comments_enabled' => Zira\Config::get('comments_enabled', 1),
                            'rating_enabled' => Zira\Config::get('rating_enabled', 0),
                            'display_author' => Zira\Config::get('display_author', 0),
                            'display_date' => Zira\Config::get('display_date', 0)
                        )
                    );
                    static::runRecordsHook($records);
                }

                // top level category records
                if ($use_cache) {
                    $top_categories = Zira\Models\Category::getHomeCategories();

                    $includeChilds = Zira\Config::get('category_childs_list', true);
                    if ($includeChilds && CACHE_CATEGORIES_LIST) {
                        $all_categories = Zira\Category::getAllCategories();
                    }
                    foreach ($top_categories as $category) {
                        // categories are cached
                        //if ($category->access_check && !Zira\Permission::check(Zira\Permission::TO_VIEW_RECORDS)) continue;

                        $rating_enabled = $category->rating_enabled !== null ? $category->rating_enabled : Zira\Config::get('rating_enabled', 0);
                        $display_author = $category->display_author !== null ? $category->display_author : Zira\Config::get('display_author', 0);
                        $display_date = $category->display_date !== null ? $category->display_date : Zira\Config::get('display_date', 0);

                        $comments_enabled = Zira\Config::get('comments_enabled', 1);
                        if ($category->comments_enabled !== null) $comments_enabled = $category->comments_enabled && $comments_enabled;

                        $childs = null;
                        if ($includeChilds && CACHE_CATEGORIES_LIST && isset($all_categories)) {
                            $childs = array();
                            foreach($all_categories as $_category) {
                                // categories are cached
                                //if ($_category->access_check && !Zira\Permission::check(Zira\Permission::TO_VIEW_RECORDS)) continue;
                                if (mb_strpos($_category->name, $category->name . '/', null, CHARSET) === 0) {
                                    $childs []= $_category;
                                }
                            }
                        }

                        $categories [] = array(
                            'category_id' => $category->id,
                            'title' => Zira\Locale::t($category->title),
                            'url' => static::generateCategoryUrl($category->name),
                            'records' => static::getRecords($category, true, $limit, null, $includeChilds, $childs, 1, false, $order),
                            'count' => 0,
                            'settings' => array(
                                'comments_enabled' => $comments_enabled,
                                'rating_enabled' => $rating_enabled,
                                'display_author' => $display_author,
                                'display_date' => $display_date
                            )
                        );
                    }

                    Zira\Cache::setArray($categories_cache_key, $categories);
                }
            }
        }
        return $categories;
    }
}