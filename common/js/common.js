/*****************************************************************************
 * elmの値とvalueが一致する場合disp_idの要素を表示する。
 *
 * @param elm 値を取得する要素/基本的にthisを渡す
 * @param value 比較値
 * @param disp_id 表示/非表示を切り替える要素ID
 *****************************************************************************/
function changeSubDisp( elm, value, disp_id )
{
	if(elm.value == value)	 { $('#'+disp_id).css('display','inline'); }
	else					 { $('#'+disp_id).css('display','none'); }
}


/*****************************************************************************
 * before要素を非表示にし、after要素を表示する
 *
 * @param before 非表示にするID
 * @param after 表示するID
 *****************************************************************************/
function switchDisplay( before, after )
{
	$('#'+before).css('display','none');
	$('#'+after).css('display','inline');
}

/*****************************************************************************
 * 全てにチェックを入れる
 *
 * @param elm チェック状況を確認する要素/基本的にthisを渡す
 * @param id 状態を変更するID
 * @param flg trueの場合チェックが外されると全てのチェックを外す。
 *****************************************************************************/
function checkAll( elm, id, flg )
{
	if( elm.checked || flg )
	{
		var doc		 = document.getElementById(id);
		var list	 = doc.getElementsByTagName("input");
		var result	 = true;
		if( !elm.checked && flg ) { result = false; }

		for (var i = 0; i < list.length; i++)
		{
			if (list[i].getAttribute("type") == "checkbox") { list[i].checked = result; }
		}

	}
}

/*****************************************************************************
 * GETパラメータを取得する。
 *****************************************************************************/
function getGetParam()
{
	var query = window.location.search.substring(1);
	var parms = {};
	$.each( query.split('&'), function(i,str){
		var p = str.indexOf('=');
		if( p > 0 ) { parms[str.substring(0,p)]= str.substring(p+1); }
	});

	return parms;
}


/*****************************************************************************
 * カラムの値から子カラムの要素を変更する。
 *
 * @param elm フォームデータ
 * @param colName 親IDが格納されているカラム
 * @param childCol 子要素のカラム
 * @param childTableName 子要素のテーブル名
 * @param childSearchCol 子要素テーブルの親IDが格納されているカラム名
 *****************************************************************************/
function loadChild(elm, colName, childCol, childTableName, childSearchCol)
{
	var form = elm.form;
	elm.blur();

	var parent;
	parent = form[colName].value;

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "json",
		data: "c=CommonApi&m=getChildJsonData&parent=" + parent + "&tableName=" + childTableName + "&parentCol=" + childSearchCol
	})
	.done(function(res){
		$(form[childCol]).removeOption(/./);
		$(form[childCol]).addOption(res, false);
	})
	.fail(function(xml, status, e){});
}


/*****************************************************************************
 * flgのチェック状況に応じてid内のチェックボックスのチェック状態を変更する。
 *
 * @param flg フラグ
 * @param id チェック状態状態を変更するチェックボックスが所属するdivのid
 *****************************************************************************/
function changeChecked( flg, id )
{
	if( flg.checked )
		$("input:checkbox", "#"+id ).attr( "checked", "checked" );
	else
		$("input:checkbox", "#"+id ).removeAttr( "checked" );

}

/*****************************************************************************
 * 指定名のcheckboxでチェックされた物のvalueを取得する。
 *
 * @param name checkbox名
 * @return チェックされた物のvalue一覧
 *****************************************************************************/
function getCheckboxValueList( name )
{
	var value = "";
	var check = $("input:checkbox[name='"+name+"[]']:checked").get();

	$(check).each( function() { value += this.value + "/"; } );

	return value.slice( 0, -1 );
}


/*****************************************************************************
 * pc/spデザイン切り替え。
 *****************************************************************************/
function setSmartPhoneDispMode( mode )
{
	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "text",
		data: "c=CommonApi&m=setSmartPhoneDispMode&mode=" + mode
	})
	.done(function(res){ window.location.reload(true); })
	.fail(function(xml, status, e){});
}

var $ASyncLoaders = new Array();

/*****************************************************************************
 * システムの機能を非同期で呼び出す
 *
 * @param $id トークンID
 *****************************************************************************/
function callASyncCC( $id )
{
	var $script    = $( 'script[data-async-cc-id=' + $id + ']' );
	var $container = $( 'script[data-async-cc-id=' + $id + ']' ).parent( '[id]' );
	var $loader    = $( 'script[data-async-cc-id=' + $id + ']' ).next( '[data-async-loader]' );

	if( $container.attr( 'id' ) && $loader.length && !$loader.filter( '[data-async-nocache]' ).length ) //コンテナとローダイメージの紐付けが可能な場合
		{ $ASyncLoaders[ $container.attr( 'id' ) ] = $loader; }

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'text',
		data: 'c=CommonApi&m=callAsyncCC&id=' + $id
	})
	.done(function( $res ){ $loader.remove();$script.after( $res ); })
	.fail(function( $xml , $status , $e ){ $loader.remove();$script.after( 'async ajax error' ); });
}

/*****************************************************************************
 * 検索結果を取得する
 *
 * @param $embedID 取得結果を取り込むコンテナ要素のID
 * @param $query   検索クエリ
 *****************************************************************************/
function embedSearch( $embedID , $query )
{
	if( $ASyncLoaders[ $embedID ] )
		{ $( '#' + $embedID ).html( $ASyncLoaders[ $embedID ] ); }

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'text',
		data: 'c=CommonApi&m=embedSearch&' + $query + '&embedID=' + $embedID
	})
	.done(function( $res ){ $( '#' + $embedID ).html( $res ); })
	.fail(function( $xml , $status , $e ){ $( '#' + $embedID ).html( 'embed error' ); });
}

/*****************************************************************************
 * 検索結果の件数を取得する
 *
 * @param $embedID 取得結果を取り込むコンテナ要素のID
 * @param $query   検索クエリ
 *****************************************************************************/
function embedSearchRow( $embedID , $query )
{
	if( $ASyncLoaders[ $embedID ] )
		{ $( '#' + $embedID ).html( $ASyncLoaders[ $embedID ] ); }

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'text',
		data: 'c=CommonApi&m=embedSearchRow&' + $query + '&embedID=' + $embedID
	})
	.done(function( $res ){ $( '#' + $embedID ).html( $res ); })
	.fail(function( $xml , $status , $e ){ $( '#' + $embedID ).html( 'embed error' ); });
}

/*****************************************************************************
 * フォームの内容を別のスクリプトに送信する
 *
 * @param $form   フォームオブジェクト
 * @param $action 送信先
 * @param $target targetを変更する場合は指定
 *****************************************************************************/
function anotherSubmit( $form , $action , $target )
{
	var $originAction = $form.action;
	var $originTarget = $form.target;

	$form.action = $action;
	$form.target = $target;

	$form.submit();

	$form.action = $originAction;
	$form.target = $originTarget;
}

/*****************************************************************************
 * フォームの連動関係を設定する
 *
 * @param $iMainName   親フォームの名前
 * @param $iSubName    子フォームの名前
 * @param $iTableName  子フォームに割り当てるテーブル名
 * @param $iColumnName 子テーブルの絞り込みに使用するカラム名
 *****************************************************************************/
function LinkageForm( $iMainName , $iSubName , $iTableName , $iColumnName , $iCCID )
	{ $( function(){ $( '[name="' + $iMainName + '"]' ).bind( 'change' , function(){ DoLinkage( $iMainName , $iSubName , $iTableName , $iColumnName , $iCCID ); } ); } ); }

function LinkageFormID( $iID , $iMainName , $iSubName , $iTableName , $iColumnName , $iCCID )
	{ $( function(){ $( '[name="' + $iMainName + '"][data-id="' + $iID + '"]' ).bind( 'change' , function(){ DoLinkageID( $iID , $iMainName , $iSubName , $iTableName , $iColumnName , $iCCID ); } ); } ); }

function DoLinkage( $iMainName , $iSubName , $iTableName , $iColumnName , $iCCID )
{
	var $value = $( '[name="' + $iMainName + '"]' ).val();

	$.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'json',
		scriptCharset: 'UTF-8',
		data: 'c=CommonApi&m=getChildJsonData&parent=' + $value + '&tableName=' + $iTableName + '&parentCol=' + $iColumnName + '&CCID=' + $iCCID
	})
	.done(function( $res ){
		$( '[name="' + $iSubName + '"] option' ).remove();

		for( var $val in $res )
			{ $( '[name="' + $iSubName + '"]' ).append( $( '<option>' ).html( $res[ $val ] ).val( $val ) ); }

		$( '[name="' + $iSubName + '"]' ).trigger( 'change' );
	});
}

function DoLinkageID( $iID , $iMainName , $iSubName , $iTableName , $iColumnName , $iCCID )
{
	var $value = $( '[name="' + $iMainName + '"][data-id="' + $iID + '"]' ).val();

	$.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'json',
		scriptCharset: 'UTF-8',
		data: 'c=CommonApi&m=getChildJsonData&parent=' + $value + '&tableName=' + $iTableName + '&parentCol=' + $iColumnName + '&CCID=' + $iCCID
	})
	.done(function( $res ){
		$( '[name="' + $iSubName + '"][data-id="' + $iID + '"] option' ).remove();

		for( var $val in $res )
			{ $( '[name="' + $iSubName + '"][data-id="' + $iID + '"]' ).append( $( '<option>' ).html( $res[ $val ] ).val( $val ) ); }

		$( '[name="' + $iSubName + '"]' ).trigger( 'change' );
	});
}

/***************************************************
指定エレメントクリックされたらチェックを全解除する

***************************************************/
(function($) {
	$.fn.untick = function(config) {
		var opts = $.extend({}, $.fn.untick.defaults, config);
		$(opts.untickElement).attr("checked","checked");
		$(opts.clickElement).on("click",function(e){
			$(opts.untickElement).removeProp("checked");
		});
	};
	$.fn.untick.defaults = {
		clickElement : "[name='clear']",
		untickElement: ".list_table :checkbox"
	};

})(jQuery);

/*****************************************************************************
 * 表示モードの切替
 *
 * @param view_mode 取得結果を取り込むコンテナ要素のID
 *****************************************************************************/
function viewChange( view_mode )
{
	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: 'text',
		data: 'c=UserApi&m=tempChangeViewMode&view_mode=' + view_mode
	})
	.done(function( $res ){ location.href="./"; })
	.fail(function( $xml , $status , $e ){});
}

//チェックボックスにチェックを入れられる数を制限する
//ops. maxGuardNum: チェックできる最大数の設定(デフォ10個)
//ex. $("input[name='hoge[]']").cbGuard({maxGuardNum:20});
(function($){
	$.fn.cbGuard = function(options){
		var $cb = $(this);
		var $config = $.extend({
			maxGuardNum: 10
		},options);
		check();
		$cb.click(function(e){check();});
		function check(){
			if($config.maxGuardNum > 0)
				{ $cb.not(':checked').attr('disabled',($cb.filter(':checked').length >= $config.maxGuardNum )); }
		}
		return (this);
	}
})(jQuery);

/*****************************************************************************
 * AWS S3の設定確認
 *****************************************************************************/
function checkUploadFile()
{
	message = "現在のアップロードファイルの保存先はシステムの設置しているサーバです。";
	jQuery.ajax({
		async : false,
		url : 'api.php',
		type : 'POST',
		dataType : "text",
		data : 'c=CommonApi&m=checkUploadFileSetting'})
		.done(function(res){
			message = res;
		})
		.fail(function(xml, status, e){ })
	;
	alert(message);
}

function InitializeDebugView() //
{
	var $debugView = $( '#debugView' );

	if( 0 >= $debugView.length ) //デバッグ画面が生成されていない場合
	{
		$debugView = $( document.createElement( 'div' ) );

		$debugView.attr( 'id' , 'debugView' );
		$debugView.css({
			'background-color' : '#333'   ,
			'color'            : '#fff'   ,
			'display'          : 'none'   ,
			'font-size'        : '14px'   ,
			'height'           : '100%'   ,
			'left'             : '0%'     ,
			'line-height'      : '18px'   ,
			'overflow'         : 'scroll' ,
			'position'         : 'fixed'  ,
			'top'              : '0%'     ,
			'width'            : '75%'    ,
			'z-index'          : '10000'  ,
		});

		$( 'body' ).append( $debugView );
		$( 'body' ).bind( 'dblclick' , ToggleDebugView );

		var $debugNotice = $( document.createElement( 'div' ) );

		$debugNotice.attr( 'id' , 'debugNotice' );
		$debugNotice.css({
			'background-color' : '#333'   ,
			'color'            : '#fff'   ,
			'font-size'        : '14px'   ,
			'font-weight'      : 'bold'   ,
			'left'             : '0%'     ,
			'line-height'      : '18px'   ,
			'padding'          : '5px'    ,
			'position'         : 'fixed'  ,
			'top'              : '0%'     ,
			'z-index'          : '10000'  ,
		});

		$( 'body' ).append( $debugNotice );
		$debugNotice.html( '【ダブルクリックでデバッグ情報を表示】' );
		window.setTimeout( function(){ $debugNotice.fadeToggle( 500 ); } , 1000 );
	}
}

function AddDebugInfo( $iDebugInfo ) //
{
	var $debugView = $( '#debugView' );

	$html = $debugView.html();

	for( $i in $iDebugInfo ) //全てのデバッグ情報を処理
		{ $html += '<div style="border-color:#999;border-style:solid;border-width:1px;margin:5px;padding:5px;">' + $i + ' : ' + $iDebugInfo[ $i ] + '</div>'; }

	$debugView.html( $html );
}

function ToggleDebugView() //
{
	var $debugView = $( '#debugView' );

	if( 0 >= $debugView.length ) //デバッグ画面が生成されていない場合
		{ InitializeDebugView(); }

	$debugView.fadeToggle( 200 );
}

(function($) {
    //詳細検索出現トグル
    $.fn.toggleShow = function(options) {
        var settings = $.extend({
            hideClass: 'none',
			targetDataName: 'target',
			toggleHtmlFlg: false,
			htmlDataName: 'html'
        }, options);
        var self = this;
        $(self).on('click', function() {
            var selector = $(this).data(settings.targetDataName);
			$(selector).toggleClass(settings.hideClass);
			if(settings.toggleHtmlFlg) {
				var html = $(this).html();
				$(this).html($(this).attr('data-' + settings.htmlDataName));
				$(this).attr('data-' + settings.htmlDataName, html);
			}
        });
    };
})(jQuery);