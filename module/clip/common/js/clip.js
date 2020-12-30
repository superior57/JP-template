/*****************************************************************************
 * クリップへの追加・削除を確認の後に行う
 *
 * @type 操作内容 regist:追加、delete:削除
 * @c_id 操作対象ID
 * @c_type 操作対象テーブル名
 *****************************************************************************/
function clipCheck( type, c_id, c_type ) 
{
	var message = new Array();
	message["regist"] = "リストに追加しますか？";
	message["delete"] = "リストから外しますか？";

	var flag = confirm ( message[type] );
	if(flag) {
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: "c=Clip&m=" + type + "&c_id=" + c_id + "&c_type=" + c_type
		})
		.done(function(res){ window.location.reload(true); })
		.fail(function(xml, status, e){ });
	}
}

/*****************************************************************************
 * 未ログイン時の表示
 *
 * @mode 操作内容
 * @type ユーザ種別 
 * @id 操作を行うユーザID
 *****************************************************************************/
function noneClip () 
{
  var message = "この機能はログイン後に利用可能となります。 ";

  var flag = confirm ( message );
  if(flag) { }
}
