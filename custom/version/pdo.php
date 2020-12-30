<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_pdo() //
		{ return ( class_exists( 'pdo' ) ? '利用可能' : false ); }
