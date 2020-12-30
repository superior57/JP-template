<?php

	include_once 'include/mvc/mvc.php';

	$controllerName = 'Preview';
	$type           = ( array_key_exists( 'type' , $_GET ) ? $_GET[ 'type' ] : null );

	ob_start();

	foreach( MVC::GetNeedIncludes( $controllerName ) as $path ) //�S�ẴC���N���[�h�p�X������
		{ include_once $path; }

	$controller = MVC::Call( $controllerName , $type );

	System::flush();
