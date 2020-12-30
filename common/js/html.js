(function(){


/*
 * getPage
 *   指定URLを読み込んで指定のidの要素としてセットする。
 *
 * 使用例
 *   getPage( { 'param' : "type=template&run=true" , 'url' : 'http://hoge.com/search.php' , 'method' : 'GET' , 'drawarea' : 'search_result' })；
 *
 *
 */
function getPage( vars )  //vars:url,method,param,drawarea
{
    jQuery.ajax({
		url: vars['url'],
		type: vars['method'] ? vars['method'] : 'POST',
		dataType: "plane/text",
		data: vars['param']
	})
    .done(function(responseText){ $('#'+vars['drawarea']).html(responseText) })
    .fail(function(xml, status, e){ $('#'+vars['drawarea']).html("error:通信エラーが発生しました。"); });
}

function setData( vars ){
    if( typeof vars['param'] == "object" )
        vars['param']['post'] = "set&js=true";
    else if(vars['param'])
        vars['param'] += "&post=set&js=true";
    else
        vars['param'] = "post=set&js=true";
    
    var data = vars['data'] ? vars['data'] : undefined;
    
    jQuery.ajax({
		url: vars['url'] ? vars['url'] : 'api.php',
		type: vars['method'] ? vars['method'] : 'POST',
		dataType: "json",
		data: vars['param']
	})
    .done(function(res){ responceAction( data , res ); })
    .fail(function(xml, status, e){ $('#message_area').html("error:通信エラーが発生しました。"); });
}
function addData( vars ){
    if( typeof vars['param'] == "object" )
        vars['param']['post'] = "add&js=true";
    else if(vars['param'])
        vars['param'] += "&post=add&js=true";
    else
        vars['param'] = "post=add&js=true";
    
    var data = vars['data'] ? vars['data'] : undefined;
    
    jQuery.ajax({
		url: vars['url'] ? vars['url'] : 'api.php',
		type: vars['method'] ? vars['method'] : 'POST',
		dataType: "json",
		data: vars['param']
	})
    .done(function(res){ responceAction( data , res ); })
    .fail(function(xml, status, e){ $('#message_area').html("error:通信エラーが発生しました。"); });
}

function getData( vars )
{
    if( typeof vars['param'] == "object" )
        vars['param']['post'] = "select&js=true";
    else if(vars['param'])
        vars['param'] += "&post=select&js=true";
    else
        vars['param'] = "post=select&js=true";
    
    var data = vars['data'] ? vars['data'] : undefined;
    
    jQuery.ajax({
		url: vars['url'] ? vars['url'] : 'api.php',
		type: vars['method'] ? vars['method'] : 'POST',
		dataType: "json",
		data: vars['param']
	})
    .done(function(res){ responceAction( data , res ); })
    .fail(function(xml, status, e){ $('#message_area').html("error:通信エラーが発生しました。"); });
}

function deleteData( vars )
{
    if( typeof vars['param'] == "object" )
        vars['param']['post'] = "delete&js=true";
    else if(vars['param'])
        vars['param'] += "&post=delete&js=true";
    else
        vars['param'] = "post=delete&js=true";
    
    var data = vars['data'] ? vars['data'] : undefined;
    
    jQuery.ajax({
		url: vars['url'] ? vars['url'] : 'api.php',
		type: vars['method'] ? vars['method'] : 'POST',
		dataType: "json",
		data: vars['param']
	})
    .done(function(res){ responceAction( data , res ); })
    .fail(function(xml, status, e){$('#message_area').html("error:エラーが発生しました。:"+status); });
}

function responceAction(data,res){
    if(!res['success']){
        data = data['error'];
    }else{
        data = data['success'];
    }
    
    switch(data['action']){
        default:
        case 'draw':
            if(data['msg'])
                $('#'+data['drawarea'] ).html( data['msg'] );
            else
                $('#'+data['drawarea'] ).html( res['msg'] );
            break;
        case 'alert':
            if(data['msg'])
                alert( data['msg'] );
            else
                alert( res['msg'] );
            break;
        case 'reload':
            window.location.reload(true);
            break;
        case 'location':
            window.location = data['url'];
            break;
        case 'func':
            data['callback']();
            break;
    }

}

//window.html_change = sendMailStart;

})();

/*
使用例

function deleteCheck ( type, id ) {
  var flag = confirm ( "この注目itemsを削除します。");
  if(flag) {
	   deleteData( {
          "param" : "&type=" + type + "&id=" + id ,
          "data" : 
            {
              "success" : { "action" : "draw" ,"drawarea" : "message_area" , "msg" : "削除に成功しました。" }
             ,"error" : { "action" : "draw" ,"drawarea" : "message_area" , "msg" : "削除に失敗しました。" }
            }
       } );
  }
}

deleteCheck( 'template', 'T0001' );

*/
