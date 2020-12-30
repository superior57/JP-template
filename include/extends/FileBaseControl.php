<?php
namespace Websquare\FileBase;

class FileBaseControl{

    private static $control=null;
    private static $FileBaseControlPath="module/filebase/";

	/**
     * FileBase クラスを返す
     * @return iFileBase
     */
     static  function getControl(){
        global $CONF_FILEBASE_FLAG;
        global $CONF_FILEBASE_ENGINE;

		if(!$CONF_FILEBASE_FLAG){ $CONF_FILEBASE_ENGINE = "Null"; }
        if(is_null(self::$control)){
			$check = false ;

			$class_file = self::$FileBaseControlPath . $CONF_FILEBASE_ENGINE . '/meta.inc';
			$check = file_exists($class_file);

			if( $check )
				{ include_once $class_file; }
			else
				{ include_once "include/base/FileBase.php"; }

            $class_name = "Websquare\FileBase\FileBase";
            if(!class_exists($class_name)){ d('Not '. $CONF_FILEBASE_ENGINE. 'Class'); }
            self::$control = new $class_name();
        }
		return self::$control;
    }
}

