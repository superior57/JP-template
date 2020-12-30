<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class NewsImportLogic extends ImportLogic
{
	var $type = 'news';
	var $check_name = 'id';
	var $id_update = false;
}
