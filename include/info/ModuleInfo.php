<?php

	/**
		@brief   モジュール設定管理クラス。
		@details モジュールに関する情報を管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup PackageInformation
	*/
	class ModuleInfo
	{
		/**
			@brief     モジュールが有効になっているか調べる。
			@exception InvalidArgumentException $iModuleName_ に無効な値を指定した場合。
			@param[in] $iModuleName_ モジュール名。
			@retval    true  モジュールが有効な場合。
			@retval    false モジュールが無効な場合。
		*/
		function IsEnable( $iModuleName_ )
		{
			Concept::IsString( $iModuleName_ )->OrThrow( 'InvalidArgument' , 'モジュール名が無効です' );

			if( class_exists( 'class_' . $iModuleName_ ) ) //モジュールクラスが存在する場合
				{ return true; }
			else //モジュールクラスが見つからない場合
				{ return false; }
		}
	}

?>
