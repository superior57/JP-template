/*****************************************************************************
 * 編集可能状態に変更する
 *
 * @param id 状態を変更するレコードID
 *****************************************************************************/
function editStartCategory(id) {
    // その他をリセット
    $("input").attr("disabled", true);
    $("select").attr("disabled", true);
    $('.before').css('display', 'block');
    $('.after').css('display', 'none');

    // 変更
    $("input", "#" + id).attr("disabled", false);
    $("select", "#" + id).attr("disabled", false);

	$('.'+id+'.before').css('display','none');
	$('.'+id+'.after').css('display','block');

    $("button", "form[name=new]").attr("disabled", true);
}

/*****************************************************************************
 * 編集可能状態に変更する
 *
 * @param id 状態を変更するレコードID
 *****************************************************************************/
function editEndCategory(id) {
    $("input", "#" + id).attr("disabled", true);
    $("select", "#" + id).attr("disabled", true);

	$('.'+id+'.before').css('display','block');
	$('.'+id+'.after').css('display','none');

    $("input", "form[name=new]").attr("disabled", false);
    $("select", "form[name=new]").attr("disabled", false);
    $("button", "form[name=new]").attr("disabled", false);
}


/*****************************************************************************
 * 確認ダイアログ表示後レコードの削除を行う
 *
 * @param tableName テーブル名
 * @param id 削除を行うレコードID
 *****************************************************************************/
function deleteCheckCategory(tableName, name, id) {
    var message = "[" + id + "][" + name + "]本当に削除してもよろしいですか？";

    var flag = confirm(message);
    if (flag) {
        jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: "c=CategoryApi&m=delete&tableName=" + tableName + "&id=" + id
		})
		.done(function(res){ window.location.reload(true); })
		.fail(function(xml, status, e){});
    }
}


/*****************************************************************************
 * レコードの並び替え
 *
 * @param tableName テーブル名
 * @param id 並び替え対象レコードID
 * @param sort_pal up/down
 *****************************************************************************/
function sortCategory( tableName, id, sort_pal )
{
	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "text",
		data: "c=CategoryApi&m=rankSort&tableName=" + tableName + "&id=" + id + "&sort_pal=" + sort_pal
	})
	.done(function(res){ window.location.reload(true); })
	.fail(function(xml, status, e){ });

}