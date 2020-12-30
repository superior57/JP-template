/*  ▼▼ 設定変数 ▼▼ */
var article_property = {
  thum_url_img  :'article__ui_head_thumbs_img',
  thum_chk      : 'thum_chk_img',
  add_icon      : 'add_icon',
  edit_form     : 'edit_form',
  close_form    : 'close_form',
  close_edit    : 'close_edit',
  error_view    : 'error_body',
  view          : 'view_parts',
  view_clone    : 'view_clone',
  e_flg         : 'e_flg',
  original_form : 'article__ui_form',
  original_form2: 'article__ui_form_sub',
  part_list     : 'parts_data',
  part_head     : 'part_start',
  part_head_class: 'part_start',
  add_part      : 'add_part',
  add_part_body : 'add_part_body',
  part_tool     : 'part-tools',
  part_add_frm  : 'part_add_frm',
  part_add_btn  : 'part_add_btn',
  items_part    :'items_part',
  items_part_close: 'items_part_close',
  tool_top      : 'top_part',
  tool_up       : 'up_part',
  tool_down     : 'down_part',
  tool_last     : 'end_part',
  tool_edit     : 'edit_part',
  tool_del      : 'delete_part',
  url_check     : 'url_check',
  mv_url_chk    : 'mv_url_chk',
  img_url_chk   : 'img_url_chk',
  update        : 'update',
  add_tag       : 'add_tag',
  tagList       : 'tag_list',
  tagDel        : 'tagDel',
  tagDel_class  : 'article__tag_del_img',
  tagList_class : 'article__tag_list',
  
};



/*  ▼▼ 関数 ▼▼ */
/*****************************************************************************
 * アイテムの追加用のフォームを表示
 *****************************************************************************/
function addPartsForm(obj, add_tag)
{
    var time = jQuery.now();
    var insert_part = $('<div />');
    insert_part.attr({
        'id' : 'part_'+time,
        'class' : article_property.part_head_class,
        'data-part_start' : article_property.part_head,
    });

    insert_part.html(add_tag);
    var parts = $(obj).parents('[data-'+article_property.part_head+']');
    if(parts.length > 0)
    { parts.before(insert_part); }
    else
    { $('#'+article_property.part_list).append(insert_part); }
}

/*****************************************************************************
 * 記事タイトル関係の保存処理
 *****************************************************************************/
function articleEdit(type){

    if(type === undefined) return ;
    var error = "";

    var $form = $('#'+article_property.original_form);
    var $formMain = $('#'+article_property.original_form2);

    switch(type){
        case 'open':
            $form.find('[name=activate]').val('4');
            break;
        case 'close':
            $form.find('[name=activate]').val('1');
            break;
    }

    if($(document).find('[data-'+article_property.e_flg+']').length > 0)
    {
        var error = "修正中アイテムの編集を完了させてください。\n";
        alert(error);
        var obj = $(document).find('[data-'+article_property.e_flg+']').parent();
        var pos = obj.offset().top;
        $("html, body").animate({scrollTop:pos});
        return ;
    }

    if($(document).find('[data-'+article_property.add_part_body+']').length > 0)
    {
        alert('追加中のアイテムがあります。\作業を完了させてください。');
        return ;
    }

    var form = document.getElementById(article_property.original_form2);

    var $inputs = $formMain.find('input[type="file"]');
    $inputs.each(function(_, input) {
        if (input.files.length > 0) return;
        $(input).prop('disabled', true);
    });
    var data = new FormData(form);

    var formArray = $form.serializeArray();
    formArray.forEach( function ( __tmp ) {
        data.append(__tmp.name, __tmp.value);
    });

    articleAjax(data).done(function(result){
        $('[class=error]').remove();
        if(result['status'] == 'success')
        {
            alert('保存しました。');
            var url = location.href;
            location.href = url;
        }else if(result['check'] !== undefined)
        {
            if(result['check'].match(/<!DOCTYPE /) || result['check'].match(/<html /)){
                drawNotSaveMsg();
            }else{
                if( result['check'] == 'upfile_err')
                {
                    var msg = "";
                    for(var index in result['err'])
                    {
                        msg += result['err'][index];
                    }
                    msg += 'アップロードしたファイルを確認してください。'
                    drawNotSaveMsg(msg);
                }
                for(var index in result){ 
                    if(index != 'check')
                    {
                        var obj = $('<div />').attr('class','error');
                        obj.html(result[index]);
                        $('[name='+index+'][type!=hidden]').after(obj);
						alert('保存できなかった箇所があります。更新箇所のエラーメッセージを確認してください。');
                    }
                }
            }
        }
    })
    .fail(function(xml, status, e){
        drawNotSaveMsgSer(xml, status, e);
    }).always( function() {
    });
}

/*****************************************************************************
 * 改行コードを<br />に変換
 *****************************************************************************/
function nl2brforParts(string){
    string = string.replace(/\r\n/g, "<br />");
    string = string.replace(/(\n|\r)/g, "<br />");
    return string;
}

/*****************************************************************************
 * リンク記述をリンクに置換
 *****************************************************************************/
function convertLink(string){
    var text = string;
    jQuery.ajax({
        url : 'm_api.php' ,
        type : 'POST',
        dataType : "text",
        data : 'c=itemsApi&m=convertLink&text='+string,
        success : function(res){
            text = res;
        }
    });
    return text;
}

/*****************************************************************************
 * URLからドメインを取得
 *****************************************************************************/
function getUri4Domein(string){
    uri = string.match(/^(([^:/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/);
    return uri[1]+uri[3];
}

/*****************************************************************************
 * エスケープ
 *****************************************************************************/
function escape_html( s ){

    return s.replace( /&/g, "＆" )
        .replace( /</g, "＜" )
        .replace( />/g, "＞" )
        .replace( /"/g, "”" )
        .replace( /'/g, "’" );

}

function escape_url( s ){
    return s.replace( /</g, "＜" )
        .replace( />/g, "＞" )
        .replace( /"/g, "”" )
        .replace( /'/g, "’" );
}

/*****************************************************************************
 * アイテムをHTML上に追加する
 * 　twitterと画像は処理が異なるため、inputHTMLTagのみ呼び出し
 *****************************************************************************/
function inputPartForm(tag,obj){
    // HTMLソース上にアイテムを追加
    inputHTMLTag(tag,obj);
    // アイテム追加後の後処理
    closedPartForm(obj);
}


/*****************************************************************************
 * アイテム追加用のフォームの削除
 *****************************************************************************/
function closedPartForm(o){
    o.remove();
}

/*****************************************************************************
 * アイテムをHTML上に追加する本処理
 *****************************************************************************/
function inputHTMLTag(tag,obj){
    obj.parents('[data-'+article_property.part_head+']').replaceWith(tag);
}

/*****************************************************************************
 * アイテム間に追加するボタンを表示できるようにする
 *****************************************************************************/
function openAddPartButton(ev, ifram){
    ev.parents('[data-'+article_property.add_part+']').after(ifram);
    ev.parents('[data-'+article_property.items_part+']').hide();
}

/*****************************************************************************
 * 編集や削除の部分を再表示
 *****************************************************************************/
function resetTools(obj)
{
    obj.find('[data-'+article_property.add_icon+']').hide();
    obj.find('[data-'+article_property.part_tool+']').hide();
    obj.find('[data-'+article_property.part_tool+']').children().show();
    obj.find('[data-'+article_property.items_part+']').hide();
    obj.find('[data-'+article_property.add_icon+']').show();
    obj.find('[data-'+article_property.part_tool+']').show();
    if( obj.next('[data-'+article_property.part_head+']').length == 0)
    {
        obj.find('[data-'+article_property.tool_last+']').hide();
        obj.find('[data-'+article_property.tool_up+']').hide();
    }
    if( obj.prev('[data-'+article_property.part_head+']').length == 0)
    {
        obj.find('[data-'+article_property.tool_top+']').hide();
        obj.find('[data-'+article_property.tool_up+']').hide();
    }

}

/*****************************************************************************
 * アイテムのエラー表示
 *****************************************************************************/
function addPartsErrorMessage(o,msg,classname,insert)
{
    if(classname === undefined || classname.length == 0) classname = 'part_err';
    if(insert === undefined || insert.length == 0) insert = 'after';

    var chk = $('<div />').attr('class',classname);
    chk.text(msg);
    
    switch (insert){
        case 'before':
            o.before(chk);
            break;
        case 'after':
            o.after(chk);
            break;
    }
}

/*****************************************************************************
 * アイテムのエラー表示を削除
 *****************************************************************************/
function delPartsErrorMessage(o,classname)
{
    if(classname === undefined || classname.length == 0) classname = 'part_err';
    o.find('.'+ classname).remove();
}

/*****************************************************************************
 * 画像URLにアクセスできるか確認
 *****************************************************************************/
function checkImageUrl( url, self, type){
    var data = new FormData
    data.append('url', url);
    data.append('type', type);
    data.append('m', 'checkImageURL');
    data.append('c', 'articleApi');

    articleAjax(data).done(function(result){
        if( result.status != 'success' ) {
            alert('URL先が確認できません。');
        }
        else {
            if( typeof result.src !== "undefined" ) {
                scraping( result.src );
            }
        }
    }).fail(function(result) {
       alert('URL先が確認できません。');
    }).always(function(){
    });
}

/*****************************************************************************
 * htmlからtitleとdescription抽出
 *****************************************************************************/
function scraping(src) {
    var $obj = $('<div />');
    $obj.html(src);

    var data = [];
    data['img'] = [];
    data['title'] = $obj.find('title').text();
    data['description'] = $obj.find('description').text();

    var $meta = $obj.find('meta');
    var $img = $obj.find('img');
    if( $meta.length > 0 ){
        $meta.each(function(i, val ){
            if( $(this).attr('property') == 'og:image' ) {
                data['img'].push($(this).attr('content'));
            }
            if( $(this).attr('property') == 'og:description' ) {
                data['description'] = ($(this).attr('content'));
            }
            if( $(this).attr('name') == 'description' ) {
                data['description'] = ($(this).attr('content'));
            }
        });
    }
    return data;
}

/*****************************************************************************
 * partの入力フォーム
 *****************************************************************************/
function drawAddForm(type, obj) {
    var o = {};
    o['parts_type'] = type;
    o['c'] = 'articleApi';
    o['m'] = 'addPartsForm';

    jQuery.ajax({
        url: 'api.php',
        type : 'POST',
        dataType : "json",
        data : o,
    })
    .done(function(res){
        if( typeof res.form !== "undefined") {
            addPartsForm(obj, res.form);
        }

    })
    .fail(function(res){
    })
    .always(function(res){
        if( obj.parents('[data-'+article_property.part_head+']').length > 0 ) { obj.children().hide();}
    });
}

/*****************************************************************************
 * articleの基本ajax
 *****************************************************************************/
function articleAjax(data){
    return jQuery.ajax({
        url: 'api.php',
        type : 'POST',
        dataType : "json",
        data : data,
        processData: false,
        contentType: false
    })

}

/*****************************************************************************
 * ajaxの失敗メッセージ
 *****************************************************************************/
function articleAjaxSendFail(){
    alert('更新に失敗しました。');
}

/*****************************************************************************
 * パーツと記事の紐づけ
 *****************************************************************************/
function partsIdList () {
    var ids = $('#'+article_property.part_list+' [name=parts_id]');
    var id = [];
    ids.each( function (i, obj) {
        var tmp = $(obj).val();
        if( tmp.length > 0){ id.push( tmp ); }
    });
    id = id.join('/');
    $('[name=parts]').val(id);
    var $sysForm = $('#'+article_property.original_form);
    var data = new FormData
    data.append('id', $sysForm.find('[name=id]').val());
    data.append('parts', id);
    data.append('m', 'updatePartsList');
    data.append('c', 'articleApi');

    articleAjax(data).done(function(result){
        if( result.status != 'success' ) {
            alert('更新に失敗しました');
        }
    }).fail(function(result) {
       alert('更新に失敗しました');
    }).always(function(){
    });
}

/*****************************************************************************
 * パーツの保存
 *****************************************************************************/
function sendPartsData(form,method) {
    return jQuery.ajax({
        url: 'api.php',
        type : 'POST',
        dataType : "json",
        data : form,
        processData: false,
        contentType: false
    })
}


function hideForm(obj) {
    obj.remove();
}

/*****************************************************************************
 * 一般的なHTTPのステータスメッセージ
 *****************************************************************************/
function httpStatusMsg($code)
{
    var msg = "";
    switch($code)
    {
        case '200':
            msg = "アクセス可能です。";
            break;
        case '204':
            msg = "表示する内容がありません。";
            break;
        case '400':
            msg = "URLの値が不正なためアクセスできません。";
            break;
        case '401':
            msg = "認証が必要なためアクセスできません。";
            break;
        case '403':
            msg = "アクセス拒否されたためアクセスできません。";
            break;
        case '404':
            msg = "ページ・画像が存在しないためアクセスできません";
            break;
    }

    if( msg == "")
    {
        if( 500 <= $code && $code < 510)
        {
            msg = "URL先のサーバエラーのためアクセスできません。";
        }
        else if( (300 <= $code && $code < 304) || $code == 307 || $code == 308)
        {
            msg = "指定されたURLはリダイレクト設定のため、違うURLにアクセスしました。";
        }
    }
    return msg;
}

/*****************************************************************************
 * urlチェックの戻り値に対するメッセージ
 *****************************************************************************/
function httpCheckMsg(code)
{
    switch (code)
    {
        case 'deny':
            msg = '登録許可されていないURLです。';
            break;
        case 'no_img_info':
            msg = "アクセス制限やリンク切れ等のため、画像情報を取得できませんでした。";
            break ;
        case 'no_info':
            msg = "アクセス制限やリンク切れ等のため、URL先の情報を取得できませんでした。";
            break ;
        default :
            msg = httpStatusMsg(code);
    }
    return msg ;
}


/*****************************************************************************
 * youtubeURL 確認
 *****************************************************************************/
function checkYoutubeUrl(string)
{
	var data = [];

	key = string.match(/https:\/\/www\.youtube\.com\/watch\?v=(.*)$/);
	if( key == null ){
		key = string.match(/^https:\/\/m\.youtube\.com\/watch\?v=(.*)$/);
	}
	if( key == null ){
		key = string.match(/^http:\/\/youtu\.be\/(.*)$/);
	}
	if( key == null ){
		key = string.match(/^https:\/\/youtu\.be\/(.*)$/);
	}
	if( key == null ){
		key = string.match(/http:\/\/www\.youtube\.com\/watch\?v=(.*)$/);
	}
	if( key != null && key[1] != null){
		var id = key[1].split('&');
		key[1] = id[0];
		string = 'https://www.youtube.com/watch?v='+key[1];
	}
	
	data['src'] = string;
	data['key'] = key;
	
	return data;
	
}

/*****************************************************************************
 * HTMLファイルの読み込み
 *****************************************************************************/
function loadTweet(obj) { 
    if( obj.find('[name="part_type"]').val() == "tweet" ){
        var view = obj.find('[data-'+article_property.view+'] .item_twitter');
        var tw_source = obj.find('[name="body_tweet"]').val()
        view.empty();
        view.html(tw_source);
        if (typeof twttr === 'undefined') {
            var twitterjs = document.createElement("script");
            twitterjs.async = true;
            twitterjs.src = '//platform.twitter.com/widgets.js';
            document.getElementsByTagName('body')[0].appendChild(twitterjs);
        }
        else {
            twttr.widgets.load();
        }
    }
}


/*****************************************************************************
 * HTMLファイルの読み込み
 *****************************************************************************/
function loadHTML(file) { 
    httpObj = new XMLHttpRequest();
    httpObj.open('GET',file+"?"+(new Date()).getTime(),true);
    // ?以降はキャッシュされたファイルではなく、毎回読み込むためのもの
    httpObj.send(null);
    httpObj.onreadystatechange = function(){
        if ( (httpObj.readyState == 4) && (httpObj.status == 200) ){
            if( httpObj.responseText.length>0 ) {
                return httpObj.responseText;
            }
        }
        return "";
    }
}

function scrollToParts( p_obj )
{
    var h = p_obj.offset();
    if( typeof h !== 'undefined') {
        var top = $(window).scrollTop(); // 画面先頭
        var bot = top + $(window).height(); // 画面最後
        var pos = h.top; //バーツ位置
        if( top > pos || bot < pos){ //画面内にない場合
            $(window).scrollTop(pos);
        }
    }
}


/*****************************************************************************
 * サーバエラー時のエラーメッセージを表示
 *****************************************************************************/
function drawNotSaveMsgSer( jqXHR, textStatus, errorThrown)
{
    var err_msg = '保存ができませんでした。'
    switch( jqXHR.status )
    {
        case 404:
        case 500:
            err_msg = '保存処理が実行できませんでした。<br />システム管理者にお問い合せください。'
            break;
        case 400:
            err_msg = '保存に失敗しました。<br />サーバーの制限にかかった可能性がありますので、<br/>画像アイテムを減らすなど一旦新規アイテム数を減らしてから保存を行なってください。';
            if( jqXHR.responseText.match(/POST\sContent-Length\sof/))
            {
                err_msg = 'データが大きすぎます。<br />新規アイテムでファイルをアップロードされた場合は、数回に分けて記事を保存してください。<br />もしくは、記事のアイテムの多いので、削減してください。'
            }
            break;
    }
    
    if( err_msg.length > 0)
    {
        drawNotSaveMsg(err_msg);
    }
}

/*****************************************************************************
 * 記事の保存失敗時のエラーメッセージを表示
 *****************************************************************************/
function drawNotSaveMsg(err_msg)
{
    alert(err_msg)
}
