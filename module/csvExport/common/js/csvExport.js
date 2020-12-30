//■処理 //

/**
	@brief     フォームを初期化して非同期アップロードを使用できる状態にする。
	@param[in] $iForm フォームオブジェクト。
*/
function csvExport_init( $iForm ) //
{
	var $form      = $( $iForm );
	var $fileForms = $( 'input[type="file"]' , $form );

	$( function(){
		$form.on( 'submit' , function(){ $fileForms.attr( 'disabled' , 'disabled' ); } );

		for( var $i = 0 ; $fileForms.length > $i ; ++$i ) //全てのfileフォームを処理
		{
			var $fileForm = $( $fileForms.get( $i ) );
			var $tmpForm  = $( 'input[name="' + $fileForm.attr( 'name' ) + '_filetmp' + '"][type="hidden"]' );
			var $type = $('[name=type]',$form).val();

			$fileForm.on( 'change' , function( $target ){
				return function(){
					csvExport_run( $target, $type ); 
				} }( $fileForm ) );

			csvExport_init_draw( $fileForm );

			if( !$tmpForm.length ){ //引き継ぎフォームがない場合
				$( '<input type="hidden" name="' + $fileForm.attr( 'name' ) + '_filetmp">' ).insertAfter( $fileForm );
			}else{
				csvExport_result_draw( $fileForm, {'preview':'<a href="'+$tmpForm.val()+'">アップロード済</a>'} );
			}
		}
	} );
}

/**
	@brief     ファイル選択フォームを非同期で送信する。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function csvExport_run( $iFileForm, $type ) //
{
	var $form     = $( $iFileForm );
	var fname = $($form.prop('files')[0]).prop('name');
	var type = fname.split('.');

	if(type[type.length -1].toLowerCase() != 'csv'){
		alert('CSVファイルを選択してください');
		return false;
	}

	if(!confirm("選択したファイルから取込を実行しますか？")){
		alert("キャンセルしました。");
		return;
	}


	var $token    = $( 'input[name="authenticity_token"]' );
	var $formdata = new FormData();
	var $tmpForm = $( 'input[name="' + $form.attr( 'name' ) + '_filetmp' + '"][type="hidden"]' );

	$formdata.append( $form.attr( 'name' ) , $form.prop( 'files' )[ 0 ] );
	$formdata.append( 'authenticity_token' , $token.val() );
	$formdata.append( 'c' , 'csvExportApi' );
	$formdata.append( 'm' , 'uploadCsv' );
	$formdata.append( 'type' , $type );
	if($tmpForm.val().match(/file\/upload\//) != null){
		$formdata.append( 'replace' , $tmpForm.val() );
	}

	$.ajax({
		'url'         : 'api.php' ,
		'type'        : 'POST' ,
		'data'        : $formdata ,
		'dataType'    : 'json' ,
		'processData' : false ,
		'contentType' : false ,
		'xhr'         : function(){ return csvExport_xhr( $iFileForm ); } ,
	}).done( function( $res ){
		csvExport_result( $iFileForm , $res );
		csvExport_importCSV($iFileForm, $res);
		
	}).fail( function(){
		csvExport_failed( $iFileForm ); 
	});

	$form.attr( 'disabled' , 'disabled' );

	csvExport_begin_draw( $iFileForm );
}

function csvExport_importCSV($iFileForm, $iResult){
	var $form     = $( $iFileForm ).parent();
	var $token    = $( 'input[name="authenticity_token"]' );
	var $formdata = new FormData();
	var $preview = $form.find( 'div[data-preview-id]' );
	$preview.html('<img src="common/img/ajax-loader.gif" alt="Loading..." style="display:block; margin:0 auto;" /><div class="mes" style="text-align:center;">CSVの処理中…</div>');

	$formdata.append( 'authenticity_token' , $token.val() );
	$formdata.append( 'c' , 'csvExportApi' );
	$formdata.append( 'm' , 'importCsv' );
	$formdata.append( 'f' , $iResult['src'] );
	$formdata.append( 'type' , $iResult['type'] );

	jQuery.ajax({
		url         : 'api.php' ,
		type        : 'POST' ,
		data        : $formdata ,
		dataType    : 'json' ,
		processData : false ,
		contentType : false ,
		timeout: 0,
	}).done(function(data, textStatus, jqXHR){
		$preview.html('<div style="word-break: break-all">'+data['preview']+'</div>');
		var $token   = $( 'input[name="authenticity_token"]' );
		$token.val(data['authenticity_token']);
	}).fail(function(jqXHR, textStatus, errorThrown){
		alert('通信エラーが発生しました');
		location.reload(); // トークンがエラーになるので再読み込み
	}).always(function(datajqXHR, textStatus, jqXHRerrorTHrown){
	});

}



/**
	@brief アップロード完了時の処理。
*/
function csvExport_result( $iFileForm , $iResult ) //
{
	var $form    = $( $iFileForm );
	var $token   = $( 'input[name="authenticity_token"]' );
	var $tmpForm = $( 'input[name="' + $form.attr( 'name' ) + '_filetmp' + '"][type="hidden"]' );

	$form.removeAttr( 'disabled' );
	$token.val( $iResult[ 'token' ] );
	$tmpForm.val( $iResult[ 'src' ] );

	var progress = $form.nextAll( 'div[data-progress-id="' + $form.attr( 'data-upload-id' ) + '"]');
        $(progress).fadeOut(2000);
        
	csvExport_result_draw( $iFileForm , $iResult );
}

/**
	@brief アップロード失敗時の処理。
*/
function csvExport_failed( $iFileForm ) //
{
	var $form = $( $iFileForm );

	$form.removeAttr( 'disabled' );

	csvExport_failed_draw( $iFileForm );
}

/**
	@brief アップロード中の処理。
*/
function csvExport_xhr( $iFileForm ) //
{
	XHR = $.ajaxSettings.xhr();

	if( XHR.upload )
	{
		XHR.upload.addEventListener( 'progress' , function( e ){ csvExport_progress_draw( $iFileForm , e.total , e.loaded ); } , false );
	}

	return XHR;
}

//■描画 //

/**
	@brief     進捗表示の初期設定をする。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function csvExport_init_draw( $iFileForm ) //
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
function csvExport_begin_draw( $iFileForm ) //
{
	var $form        = $( $iFileForm );
	var $preview     = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );
	var $progress    = $( 'div[data-progress-id="' + $form.attr( 'data-upload-id' ) + '"]' );
	var $progressBar = $( 'div[data-progress-bar-id="' + $form.attr( 'data-upload-id' ) + '"]' );

        $progress.show();
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
function csvExport_progress_draw( $iFileForm , $iTotalSize , $iUploadedSize ) //
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
function csvExport_result_draw( $iFileForm , $iResult ) //
{
	var $form    = $( $iFileForm );
	var $preview = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );

	$preview.html( $iResult[ 'preview' ] );
}

/**
	@brief     進捗表示を完了する。
	@param[in] $iFileForm ファイル選択フォーム。
*/
function csvExport_failed_draw( $iFileForm ) //
{
	var $form    = $( $iFileForm );
	var $preview = $( 'div[data-preview-id="'  + $form.attr( 'data-upload-id' ) + '"]' );

	$preview.html( 'アップロード失敗' );
}

/**
 *  GETパラメータを配列にして返す
 *  
 *  @return     パラメータのObject
 *
 */
var getUrlVars = function(){
    var vars = {}; 
    var param = location.search.substring(1).split('&');
    for(var i = 0; i < param.length; i++) {
        var keySearch = param[i].search(/=/);
        var key = '';
        if(keySearch != -1) key = param[i].slice(0, keySearch);
        var val = param[i].slice(param[i].indexOf('=', 0) + 1);
        if(key != '') vars[key] = decodeURI(val);
    } 
    return vars; 
}

$(function(){
	$('[name=csvExport').on('click', function(){
		var form = $(this).parents('form');
		$('<input>').attr({
			'type': 'hidden',
			'name': 'csvExport',
			'value': 'dummy'
		}).appendTo(form);
		var query = getUrlVars();
		var sort = query['sort'];
		var sort_PAL = query['sort_PAL'];
		if(sort != '' && sort_PAL != ''){
			$('<input>').attr({
				'type': 'hidden',
				'name': 'sort',
				'value': sort
			}).appendTo(form);
			$('<input>').attr({
				'type': 'hidden',
				'name': 'sort_PAL',
				'value': sort_PAL
			}).appendTo(form);
		}
		form.submit();
	});
});
