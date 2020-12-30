/*****************************************************************************
 * 表示モードの切替
 *
 * @param view_mode 取得結果を取り込むコンテナ要素のID
 *****************************************************************************/
function viewChange( view_mode ,redirect_url )
{
    if(!redirect_url) redirect_url = "./";
    jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'text',
		data: 'c=viewModeApi&m=tempChangeViewMode&view_mode=' + view_mode
	})
    .done(function( $res ){
        location.href=redirect_url;
    })
    .fail(function( $xml , $status , $e ){});
}

