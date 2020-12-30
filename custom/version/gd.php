<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_gd() //
	{
		if( !function_exists( 'gd_info' ) )
			{ return false; }

		$info = gd_info();

		return $info[ 'GD Version' ];
	}
