<?php

    $moduleName = 'article';

    $MODULES[ $moduleName ] = Array(
        'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
        'summary' => 'プロック記事作成' , ///<このモジュールの説明。
    );

    /**********   article     **********/
    include_once './module/' . $moduleName . '/article.php';
    include_once './module/' . $moduleName . '/custom/api/articleApi.php';
    include_once './module/' . $moduleName . '/custom/view/articleView.php';
    include_once './module/' . $moduleName . '/custom/model/article.php';
    include_once './module/' . $moduleName . '/custom/model/article_parts.php';
    include_once './module/' . $moduleName . '/custom/logic/articleLogic.php';
    include_once './module/' . $moduleName . '/custom/logic/article_partsLogic.php';
//    include_once './module/' . $moduleName . '/custom/view/articleView.php';
    include_once './module/' . $moduleName . '/custom/view/article_partsView.php';

    include_once './module/' . $moduleName . '/custom/logic/article_tagLogic.php';
    include_once './module/' . $moduleName . '/custom/logic/article_categoryLogic.php';


    //■テーブル定義
    $EDIT_TYPE                             = 'article';
    $TABLE_NAME[]                          = $EDIT_TYPE;
    $THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]  = false;
    $THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]  = Array('admin');
    $THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]    = Array('admin');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ]  = Array('admin' => 'owner' );
    $LST[ $EDIT_TYPE ]                     = '[article]/article.csv';
    $TDB[ $EDIT_TYPE ]                     = '[article]/article.csv';
    $ID_HEADER[ $EDIT_TYPE ]               = '';
    $ID_LENGTH[ $EDIT_TYPE ]               = 10;
//    $ADD_LST[ 'nUser' ][ 'extends' ]       = '[article]/add/extends.csv';

    $EDIT_TYPE                             = 'article_parts';
    $TABLE_NAME[]                          = $EDIT_TYPE;
    $THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]  = false;
    $THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]  = Array('admin','cUser','nUser');
    $THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]    = Array('admin','cUser','nUser');
    $LST[ $EDIT_TYPE ]                     = '[article]/article_parts.csv';
    $TDB[ $EDIT_TYPE ]                     = '[article]/article_parts.csv';
    $ID_HEADER[ $EDIT_TYPE ]               = '';
    $ID_LENGTH[ $EDIT_TYPE ]               = 10;

    $EDIT_TYPE                             = 'article_category';
    $TABLE_NAME[]                          = $EDIT_TYPE;
    $THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]  = false;
    $LST[ $EDIT_TYPE ]                     = '[article]/article_category.csv';
    $TDB[ $EDIT_TYPE ]                     = '[article]/article_category.csv';
    $ID_HEADER[ $EDIT_TYPE ]               = '';
    $ID_LENGTH[ $EDIT_TYPE ]               = 10;

    $EDIT_TYPE                             = 'article_tag';
    $TABLE_NAME[]                          = $EDIT_TYPE;
    $THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]  = Array('admin','cUser','nUser');
    $THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]    = Array('admin','cUser','nUser');
    $THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]  = false;
    $LST[ $EDIT_TYPE ]                     = '[article]/article_tag.csv';
    $TDB[ $EDIT_TYPE ]                     = '[article]/article_tag.csv';
    $ID_HEADER[ $EDIT_TYPE ]               = '';
    $ID_LENGTH[ $EDIT_TYPE ]               = 10;

    $EDIT_TYPE                             = 'article_relationship';
    $TABLE_NAME[]                          = $EDIT_TYPE;
    $THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]  = Array('admin','cUser','nUser');
    $THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]    = Array('admin','cUser','nUser');
    $THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]  = false;
    $LST[ $EDIT_TYPE ]                     = '[article]/article_relationship.csv';
    $TDB[ $EDIT_TYPE ]                     = '[article]/article_relationship.csv';
    $ID_HEADER[ $EDIT_TYPE ]               = '';
    $ID_LENGTH[ $EDIT_TYPE ]               = 20;

    $EDIT_TYPE                             = 'article_conf';
    $TABLE_NAME[]                          = $EDIT_TYPE;
    $THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]  = Array('admin');
    $THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]    = Array('admin');
    $THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]  = false;
    $LST[ $EDIT_TYPE ]                     = '[article]/article_conf.csv';
    $TDB[ $EDIT_TYPE ]                     = '[article]/article_conf.csv';
    $ID_HEADER[ $EDIT_TYPE ]               = '';
    $ID_LENGTH[ $EDIT_TYPE ]               = 5;

    $API_ALLOW_LIST['mod_articleApi']         = array('all');

    $js_file_paths[ 'all' ][ 'jquery-ui' ]     = 'module/' . $moduleName . '/common/js/jquery-ui.min.js';
    $js_file_paths[ 'all' ][ 'article' ]       = 'module/' . $moduleName . '/common/js/article.js';
    $js_file_paths[ 'all' ][ 'article2' ]      = 'module/' . $moduleName . '/common/js/article2.js';

    $css_file_paths[ 'all' ][ 'article' ]         = 'module/' . $moduleName . '/common/css/article.css';
    $css_file_paths[ 'admin' ][ 'article_admin' ] = 'module/' . $moduleName . '/common/css/admin.css';
    $css_file_paths[ 'cUser' ][ 'article_admin' ] = 'module/' . $moduleName . '/common/css/admin.css';
    $css_file_paths[ 'nUser' ][ 'article_admin' ] = 'module/' . $moduleName . '/common/css/admin.css';

    $sp_css_file_paths[ 'all' ][ 'article' ]         = 'module/' . $moduleName . '/common/css/sp/article.css';

    if( !class_exists('Net_IDNA2') ){
        include_once 'module/' . $moduleName . '/pear/Net_IDNA2/Net/IDNA2.php';
    }
    $IDNA2 = Net_IDNA2::getInstance();

?>