<?php

	if( !isset( $MODULES ) ) //モジュール配列が未定義の場合
		{ $MODULES = Array(); }

	class PathUtil
	{
		static function ModifyTemplateFilePath( $iTemplatePath )
		{
			global $template_path;

			if( preg_match( '/^\[(\w+)\]\/(.*)$/' , $iTemplatePath , $matches ) )
				{ return './module/' . $matches[ 1 ] . '/' . $template_path . $matches[ 2 ]; }

			return $template_path . $iTemplatePath;
		}

		static function ModifyLSTFilePath( $iLSTPath )
		{
			global $lst_path;

			if( preg_match( '/^\[(\w+)\]\/(.*)$/' , $iLSTPath , $matches ) )
			 {
				if( is_file( './module/' . $matches[ 1 ] . '/' . $lst_path . $matches[ 2 ] ) )
					{ return './module/' . $matches[ 1 ] . '/' . $lst_path . $matches[ 2 ]; }
			}

			return $lst_path . $iLSTPath;
		}

		static function ModifyTDBFilePath( $iTDBPath )
		{
			global $tdb_path;

			if( preg_match( '/^\[(\w+)\]\/(.*)$/' , $iTDBPath , $matches ) )
			{
				if( is_file( './module/' . $matches[ 1 ] . '/' . $tdb_path . $matches[ 2 ] ) )
					{ return './module/' . $matches[ 1 ] . '/' . $tdb_path . $matches[ 2 ]; }
			}

			return $tdb_path . $iTDBPath;
		}

		static function ModifyIndexFilePath( $iTDBPath )
		{
			global $index_path;

			if( preg_match( '/^\[(\w+)\]\/(.*)$/' , $iTDBPath , $matches ) )
			{
				if( is_file( './module/' . $matches[ 1 ] . '/' . $index_path . $matches[ 2 ] ) )
					{ return './module/' . $matches[ 1 ] . '/' . $index_path . $matches[ 2 ]; }
			}

			return $index_path . $iTDBPath;
		}

		static function ModifySystemFilePath( $iTableName , $iPriority = null )
		{
			global $MODULES;
			global $system_path;

			if( $iPriority )
			{
				if( is_file( './module/' . $iPriority . '/' . $system_path . $iTableName . 'System.php' ) )
					{ return './module/' . $iPriority . '/' . $system_path . $iTableName . 'System.php'; }
			}

			if( is_file( $system_path . $iTableName . 'System.php' ) )
				{ return$system_path . $iTableName . 'System.php'; }

				foreach( $MODULES as $name => $option )
				{
					if( is_file( './module/' . $name . '/' . $system_path . $iTableName . 'System.php' ) )
						{ return './module/' . $name . '/' . $system_path . $iTableName . 'System.php'; }
				}

			return $system_path . $iTableName . 'System.php';
		}
	}
