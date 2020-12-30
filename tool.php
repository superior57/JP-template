<?php

	include_once 'app/adminTool/conf.php';

	MVC::SetMVCPath( 'app/adminTool/mvc/' );
	MVC::SetExMVCPath( 'app/adminTool/mvcEx/' );

	if( $SYSTEM_INSTALL_STATUS[ 'disableTool' ] ) //tool.phpが無効になっている場合
	{
		header( 'Location:index.php' );
		exit();
	}

	$controllerName = ( array_key_exists( 'app_controller' , $_REQUEST ) ? $_REQUEST[ 'app_controller' ] : 'Index' );
	$type           = null;

	ob_start();

	foreach( MVC::GetNeedIncludes( $controllerName ) as $path ) //全てのインクルードパスを処理
		{ include_once $path; }

	$controller = MVC::Call( $controllerName , $type );

	ob_end_flush();
