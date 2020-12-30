<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class AdminImportLogic extends ImportLogic
{
	var $type = 'admin';
	var $check_name = 'id';
	var $id_update = false;
}
