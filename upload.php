<?php

	include_once 'custom/head_main.php';

	ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' );

	$result              = Array();
	$result[ 'preview' ] = '';
	$result[ 'src' ]     = '';
	
	foreach( $_FILES as $data )
	{
		$ext      = preg_replace( '/^.*\.(.*)$/' , '$1' , $data[ 'name' ] );
		$saveName = 'file/upload/' . time() . '_' . rand() . '.' . $ext;

		$fileinfo = getimagesize( $data[ 'tmp_name' ] );
		$min_type = $fileinfo[ 'mime' ];

		if( isset( $data[ 'is_big' ] ) )
			{ rename( $data[ 'tmp_name' ] , $saveName ); }
		else
			{ move_uploaded_file( $data[ 'tmp_name' ] , $saveName ); }

		$FileBase->upload($saveName,$saveName);

		$result[ 'src' ]          = $saveName;
		$result[ 'size' ]         = $data[ 'size' ];
		$result[ 'type' ]         = $min_type;
		$result[ 'orginal_name' ] = $data[ 'name' ];

		if( strpos($min_type, 'image/') !== false)
		{
			if( 1024 * 1024 > $data[ 'size' ] )
				{ $result[ 'preview' ] .= '<img src="' . $saveName . '">'; }
			else
				{ $result[ 'preview' ] .= 'サイズが大きいため、プレビューは表示できません'; }

			$size = $FileBase->getimagesize($saveName);
			$result[ 'width' ]  = $size[ 0 ];
			$result[ 'height' ] = $size[ 1 ];
		}
		else{
			{ $result[ 'preview' ] .= '<p><a target="_blank" href="' . $saveName . '">アップロードファイル</a></p>'; }
		}
	}

	$result[ 'token' ] = SystemUtil::getAuthenticityToken();

	print json_encode( $result );
