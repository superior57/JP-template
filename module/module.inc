<?PHP
//command_baseの読み込み
include_once "./include/base/Util.php";
include_once "./custom/global.php";

//モジュール設定の読み込み
include_once './custom/extends/moduleConf.php';


include_once './module/news.inc';
include_once './module/zip.inc';

//system
include_once './module/thumbnail.inc';
include_once './module/accountLock.inc';
include_once './module/super_user.inc';
?>