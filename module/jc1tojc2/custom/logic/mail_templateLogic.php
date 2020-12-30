<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Mail_templateImportLogic extends ImportLogic
{
	var $type = 'mailTemplate';
	var $check_name = 'id';
	var $id_update = false;
}
