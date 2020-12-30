<?php

    $moduleName = 'sitemap';

    $MODULES[$moduleName] = array(
        'version' => '1.0',                 // このモジュールのバージョン情報。
        'summary' => 'サイトマップ生成',    // このモジュールの説明。
    );

    $CHILD_SITEMAP_DIRECTORY    = '';		// 子サイトマップのディレクトリパス
											// 「ディレクトリ/」の形式で指定
	// 指定するとsitemapの仕様に合致しないファイルを出力しますので、
	// '' のままにしておいてください。

    require_once './module/' . $moduleName . '/custom/api/sitemapApi.php';
    require_once './module/' . $moduleName . '/custom/logic/sitefileexamin.php';
    require_once './module/' . $moduleName . '/custom/logic/SitemapLogic.php';
    require_once './module/' . $moduleName . '/custom/view/sitemapView.php';
    require_once './module/' . $moduleName . '/sitemap.php';

    require_once './custom/cron/core.inc';
    require_once './module/' . $moduleName . '/custom/cron/sitemapCron.php';
    CodeScheduler::SetCron('sitemapUpdate', 'sitemapCron', 'updatesitemap');


    /**********          sitemap_confの定義          **********/

    $EDIT_TYPE                              = 'sitemap_conf';
    $TABLE_NAME[]                           = $EDIT_TYPE;
    $JP_TABLE_NAME[$EDIT_TYPE]              = 'サイトマップの設定';
    $THIS_TABLE_IS_USERDATA[$EDIT_TYPE]     = false;
    $LOGIN_KEY_COLUM[$EDIT_TYPE]            = null;
    $LOGIN_PASSWD_COLUM[$EDIT_TYPE]         = null;
    $LST[$EDIT_TYPE]                        = '[sitemap]/sitemap_conf.csv';
    $TDB[$EDIT_TYPE]                        = '[sitemap]/sitemap_conf.csv';
    $ID_HEADER[$EDIT_TYPE]                  = '';
    $ID_LENGTH[$EDIT_TYPE]                  = 0;

    $API_ALLOW_LIST['mod_sitemapApi']   = array('all');
    $js_file_paths['admin']['sitemap_conf'] = './module/' . $moduleName . '/common/js/sitemap.js';
