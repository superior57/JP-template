<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class PageImportLogic extends ImportLogic
{
	var $type = 'page';
	var $check_name = 'id';
	var $id_update = false;
}
