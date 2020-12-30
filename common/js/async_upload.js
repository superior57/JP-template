//■処理 //

/**
	@brief     フォームを初期化して非同期アップロードを使用できる状態にする。
	@param[in] $iForm フォームオブジェクト。
*/
function async_upload_init( $iForm ) //
{
	var $form      = $( $iForm );
	var $fileForms = $( 'input[type="file"]' , $form );

	$( function(){
		$form.on( 'submit' , function(){ $fileForms.attr( 'disabled' , 'disabled' ); } );

		for( var $i = 0 ; $fileForms.size() > $i ; ++$i ) //全てのfileフォームを処理
		{
			var $fileForm = $( $fileForms.get( $i ) );
			var $tmpForm  = $( 'input[name="' + $form.attr( 'name' ) + '_filetmp' + '"][type="hidden"]' );

			$fileForm.on( 'change' , function( $target ){ return function(){ async_upload_run( $target ); } }( $fileForm ) );

			async_upload_init_draw( $fileForm );

			if( !$tmpForm.size() ) //引き継ぎフォームがない場合
				{ $( '<input type="hidden" name="' + $fileForm.attr( 'name' ) + '_filetmp">' ).insertAfter( $fileForm ); }
		}
	} );
}

/**
	@brief     ファイル選択フォームを非同期で送信する。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function async_upload_run( $iFileForm ) //
{
	var $form     = $( $iFileForm );
	var $token    = $( 'input[name="authenticity_token"]' );
	var $formdata = new FormData();

	$formdata.append( $form.attr( 'name' ) , $form.prop( 'files' )[ 0 ] );
	$formdata.append( 'authenticity_token' , $token.val() );

	$.ajax({
		'url'         : 'upload.php' ,
		'type'        : 'POST' ,
		'data'        : $formdata ,
		'dataType'    : 'json' ,
		'processData' : false ,
		'contentType' : false ,
		'xhr'         : function(){ return async_upload_xhr( $iFileForm ); } ,
	}).done( function( $res ){ async_upload_result( $iFileForm , $res ); } ).fail( function(){ async_upload_failed( $iFileForm ); } );

	$form.attr( 'disabled' , 'disabled' );

	async_upload_begin_draw( $iFileForm );
}

/**
	@brief アップロード完了時の処理。
*/
function async_upload_result( $iFileForm , $iResult ) //
{
	var $form    = $( $iFileForm );
	var $token   = $( 'input[name="authenticity_token"]' );
	var $tmpForm = $( 'input[name="' + $form.attr( 'name' ) + '_filetmp' + '"][type="hidden"]' );

	$form.removeAttr( 'disabled' );
	$token.val( $iResult[ 'token' ] );
	$tmpForm.val( $iResult[ 'src' ] );

	async_upload_result_draw( $iFileForm , $iResult );
}

/**
	@brief アップロード失敗時の処理。
*/
function async_upload_failed( $iFileForm ) //
{
	var $form = $( $iFileForm );

	$form.removeAttr( 'disabled' );

	async_upload_failed_draw( $iFileForm );
}

/**
	@brief アップロード中の処理。
*/
function async_upload_xhr( $iFileForm ) //
{
	XHR = $.ajaxSettings.xhr();

	if( XHR.upload )
	{
		XHR.upload.addEventListener( 'progress' , function( e ){ async_upload_progress_draw( $iFileForm , e.total , e.loaded ); } , false );
	}

	return XHR;
}

//■描画 //

/**
	@brief     進捗表示の初期設定をする。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function async_upload_init_draw( $iFileForm ) //
{
	var $form = $( $iFileForm );

	$form.attr( 'data-upload-id' , Math.random() );
	$( '<div data-preview-id="' + $form.attr( 'data-upload-id' ) + '">&nbsp;</div>' ).insertAfter( $form );
	$( '<div data-progress-id="' + $form.attr( 'data-upload-id' ) + '""><div data-progress-bar-id="' + $form.attr( 'data-upload-id' ) + '"">&nbsp;</div></div>' ).insertAfter( $form );
}

/**
	@brief     進捗表示を開始する。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function async_upload_begin_draw( $iFileForm ) //
{
	var $form        = $( $iFileForm );
	var $preview     = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );
	var $progress    = $( 'div[data-progress-id="' + $form.attr( 'data-upload-id' ) + '"]' );
	var $progressBar = $( 'div[data-progress-bar-id="' + $form.attr( 'data-upload-id' ) + '"]' );

	$progress.css( 'background-color' , '#cccccc' );
	$progress.css( 'border-color'     , '#cccccc' );
	$progress.css( 'border-radius'    , '10px' );
	$progress.css( 'border-style'     , 'solid' );
	$progress.css( 'border-width'     , '0px' );
	$progress.css( 'width'            , '300px' );

	$progressBar.css( 'background-color' , '#3399ff' );
	$progressBar.css( 'border-color'     , '#3399ff' );
	$progressBar.css( 'border-radius'    , '10px' );
	$progressBar.css( 'border-style'     , 'solid' );
	$progressBar.css( 'border-width'     , '0px' );

	$preview.html( 'アップロード中...' );
}

/**
	@brief     進捗表示を進行させる。
	@param[in] $iFileForm     ファイル選択フォーム。
	@param[in] $iTotalSize    ファイルの合計サイズ。
	@param[in] $iUploadedSize アップロード済みのサイズ。
*/
function async_upload_progress_draw( $iFileForm , $iTotalSize , $iUploadedSize ) //
{
	var $form        = $( $iFileForm );
	var $preview     = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );
	var $progress    = $( 'div[data-progress-id="' + $form.attr( 'data-upload-id' ) + '"]' );
	var $progressBar = $( 'div[data-progress-bar-id="' + $form.attr( 'data-upload-id' ) + '"]' );
	var $parcent     = parseInt( $iUploadedSize / $iTotalSize * 10000 ) / 100;

	$preview.html( $parcent + '%' );
	$progressBar.css( 'width' , $parcent + '%' );
}

/**
	@brief     進捗表示を完了する。
	@param[in] $iFileForm ファイル選択フォーム。
	@param[in] $iResult   アップロードAPIの返り値。
*/
function async_upload_result_draw( $iFileForm , $iResult ) //
{
	var $form    = $( $iFileForm );
	var $preview = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );

	$preview.html( $iResult[ 'preview' ] );
}

/**
	@brief     進捗表示を完了する。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function async_upload_failed_draw( $iFileForm ) //
{
	var $form    = $( $iFileForm );
	var $preview = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );

	$preview.html( 'アップロード失敗' );
}

