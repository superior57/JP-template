/*****************************************************************************
 * 一括操作処理分岐
 *
 * @param type nUser,cUser
 *****************************************************************************/
function bulkOperations( type )
{
	switch( $("#blukOperations").val() )
	{
	case'sendMail': // 検索結果にメールを送信
		window.location = './index.php?app_controller=register&type=mailSend&user_type=&receive_id=&'+window.location.search;
		break;
	case'registerList': // 検索結果をリストに登録
		window.location = './index.php?app_controller=register&type=list&user_type='+type+location.search.replace('?','&').replace('&type='+type,'');
		break;

	case'deleteUser': // 仮登録ユーザーを一括削除
		deleteActivateNoneUser(type);
		break;
	}
}

/*****************************************************************************
 * 選択ユーザー操作処理分岐
 *
 * @param type nUser,cUser
 *****************************************************************************/
function selectOperations( type )
{
	id = getCheckboxValueList('id');
	if( id.length == 0 ) { alert("ユーザーがチェックされていません。"); return; }

	switch( $("#selectOperations").val() )
	{
	case'sendMail': // チェックしたユーザーにメール送信
		mailsend_form.submit();
		break;
	case'deleteUser': // チェックしたユーザーを削除
		deleteSelectUser( type, id );
		break;
	}
}

/*****************************************************************************
 * 確認後、チェックされたユーザーを削除
 *
 * @param type nUser,cUser
 *****************************************************************************/
function deleteSelectUser( type, id )
{
	var flag = confirm( 'チェックしたユーザーを削除してもよろしいですか？' );
	if(flag)
	{
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: "c=UserApi&m=deleteSelectUser&type=" + type + "&idList=" + id
		})
		.done(function(res){ window.location.reload(true);})
		.fail(function(xml, status, e){});
	}
}

/*****************************************************************************
 * 確認後、仮登録のまま3日経過しているユーザを削除。
 *
 * @param type nUser,cUser
 *****************************************************************************/
function deleteActivateNoneUser( type )
{
	var flag = confirm( '仮登録のまま3日以上経過しているユーザーを削除してもよろしいですか？' );
	if(flag)
	{
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: "c=UserApi&m=deleteActivateNone&type=" + type
		})
		.done(function(res){ window.location.reload(true); })
		.fail(function(xml, status, e){});
	}
}

/*****************************************************************************
 * 確認後、求人情報に課金方式を一括設定。
 *
 * @param charges apply/employment
 *****************************************************************************/
function setJobCharges( charges )
{

	if(charges){
		var chargesName = new Array();
		chargesName['apply'] = '応募課金';
		chargesName['employment'] = '採用課金';

		var flag = confirm( '求人情報の課金方式を'+chargesName[charges]+'に一括設定してもよろしいですか？' );
		if(flag)
		{
			jQuery.ajax({
				url : 'api.php' ,
				type : 'POST',
				dataType : "text",
				data : "c=JobApi&m=setCharges&charges="+charges
			})
			.done(function(res){ confirm( res+'件設定しました' ); })
			.fail(function(xml, status, e){});
		}
	}else{
		alert('選択して下さい');
	}
}

/*****************************************************************************
 * 確認後、求人企業に有効期間を一括設定。
 *****************************************************************************/
function setcUserLimit(type)
{
	if(type != "mid" && type != "fresh"){
		alert("不正なリクエストです。");
		return;
	}

	if(type == "mid")
		{ var prefix = "m_"; }
	else if(type == "fresh")
		{ var prefix = "f_"; }

	var year = $("#"+prefix+"year").val();
	var month = $("#"+prefix+"month").val();
	var day = $("#"+prefix+"day").val();

	if( year.length > 0 && month.length > 0 && day.length > 0 )
	{
		var flag = confirm( '求人企業の有効期間を'+year+'年'+month+'月'+day+'日に一括設定してもよろしいですか？' );
		if(flag)
		{
			jQuery.ajax({
				url: 'api.php',
				type: 'POST',
				dataType: "text",
				data: "c=UserApi&m=setLimitAll&type=" + type + "&year=" + year + "&month=" + month + "&day=" + day
			})
			.done(function(res) {
				switch (res) {
					case "unsetUserLimitConf":
						alert('先に利用期間課金を有効に設定して下さい｡');
						break;
					default:
						alert(res + '社設定しました');
						break;
				}
			})
			.fail(function(xml, status, e){
				alert('エラーにより処理は中断されました。');
			});
		}
	}
	else { alert('期間を指定して下さい'); }
}

/*****************************************************************************
 * 確認後、求人企業に課金方法を一括設定。
 *
 * @param charges ul_term/job
 *****************************************************************************/
function setcUserCharges( charges )
{
	var flag = confirm( '求人企業の課金方法を求人毎に一括設定してもよろしいですか？' );
	if(flag)
	{
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: "c=UserApi&m=setCharges&charges=" + charges
		})
		.done(function(res){
			switch(res){
				case "unsetAppEmpConf":
					alert('先に従量課金プランを有効に設定して下さい｡');
					break;
				default:
					alert( res+'社設定しました' );
					break;
			}
		})
		.fail(function(xml, status, e){});
	}
}

/*****************************************************************************
 * 確認後、求人件数を事前集計。
 *****************************************************************************/
function preAggregated()
{
	var flag = confirm( '求人情報の事前集計行なってもよろしいですか？\n※求人件数によっては実行に大幅に時間がかかりますのでご注意下さい。' );
	if(flag)
	{
		$("#loader").html( '<img src="common/img/ajax-loader.gif" border="0" />' );

		jQuery.ajax({
			url: 'cron.php',
			type: 'GET',
			dataType: "text",
			data: "label=countUpdate"
		})
		.done(function(res){ $("#loader").html(''); confirm( '集計が完了しました' ); })
		.fail(function(xml, status, e){});
	}
}


/*****************************************************************************
 * 確認後、求人での表示を変更。
 *****************************************************************************/
function editAddsDisp()
{
	idList = getCheckboxValueList('disp');
	if( idList.length == 0 ) { alert("1つ以上都道府県を表示して下さい"); return; }

	var flag = confirm( '求人で表示の変更を反映してもよろしいですか？' );
	if(flag)
	{
		jQuery.ajax({
			url : 'api.php' ,
			type : 'POST',
			dataType : "text",
			data : "c=AreaApi&m=editAddsDisp&idList="+idList
		})
		.done(function(res){ confirm( '反映しました' ); })
		.fail(function(xml, status, e){});
	}
}

/*****************************************************************************
 * 市区町村レコードの並び替え
 *
 * @param tableName テーブル名
 * @param id 並び替え対象レコードID
 * @param sort_pal up/down
 * @param adds_id 都道府県レコードID
 *****************************************************************************/
function addSubSortCategory( tableName, id, sort_pal, adds_id )
{
	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "text",
		data: "c=AreaApi&m=rankSort&tableName=" + tableName + "&id=" + id + "&sort_pal=" + sort_pal + "&adds_id=" + adds_id
	})
	.done(function(res){ window.location.reload(true); })
	.fail(function(xml, status, e){ });
}

/*****************************************************************************
 * 表示範囲選択可否の切り替え
 *****************************************************************************/
function topAreaDisp()
{
	switch( $("input:checked[name='top_area']").val() )
	{
	case 'all':
	case 'job':
		$("select[name='top_area_range']").removeProp("disabled");
		topAreaSubDisp();
		break;
	default:
		$("select[name='top_area_range']").attr("disabled", "disabled");
		$('#top_area_area_disp').css('display','none');
		$('#top_area_adds_disp').css('display','none');
		break;
	}
}

/*****************************************************************************
 * エリア指定/都道府県表示の切り替え
 *****************************************************************************/
function topAreaSubDisp()
{

	switch( $("select[name='top_area_range']").val() )
	{
	case 'all':
		$("select[name=def_adds]").val("");
		$('#top_area_area_disp').css('display','none');
		$('#top_area_adds_disp').css('display','none');
		break;
	case 'area':
		$('#top_area_area_disp').css('display','inline');
		$('#top_area_adds_disp').css('display','none');
		$("select[name=def_adds]").val("");
		break;
	case 'adds':
		$('#top_area_area_disp').css('display','none');
		$('#top_area_adds_disp').css('display','inline');
		break;
	default:
		$('#top_area_area_disp').css('display','none');
		$('#top_area_adds_disp').css('display','none');
		break;
	}

}

/*****************************************************************************
* 利用不可理由のフォームを表示/非表示
*****************************************************************************/
(function($) {
    $.fn.toggleDenyReasonForm = function(denyID) {
        function toggle(value){
            var ACTIVE_DENY = 8;
            if(value == ACTIVE_DENY)
                $(denyID).show();
            else
                $(denyID).hide();
        }

        var radio = $(this);
        radio.on("change",function(){
            toggle(this.value);
        });
        return this.each(
            function(){
                var radio = $(this);
				if(radio.prop("checked"))
					toggle(radio.val());
            }
        );
    };
})(jQuery);