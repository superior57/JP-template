<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Entry_progressImportLogic extends ImportLogic
{
	var $type = 'entry_progress';
	var $check_name = 'id';
	var $id_update = false;
	var $delete = true;
}
