var addHACount=1;
$(function(){

	$("input[name=hope_work_place]").each(function(){
		if($(this).attr("id")!="hope_work_place" && $("#hope_work_place").val()!=undefined) $(this).remove();
	});

	ha_init();
	$("select[name=has_add_sub]").on("change",function(){ able2add_ha(); });
	$("#drawhope_work_placeSelecter").append("<input type=\"button\" name=\"add_ha\" value=\"希望勤務地を追加\" disabled />");

	$("input[name=add_ha]").on("click",function(){

		if($("input[name=hope_work_place]").val()){
			var hope_work_place=$("input[name=hope_work_place]").val().split("/");
		}else{
			var hope_work_place=new Array();
		}

		hope_work_place.push($("select[name=has_adds] option:selected").val()+","+$("select[name=has_add_sub] option:selected").val());
		var selectAddSubId=$("select[name=has_add_sub] option:selected").val();

		$("input[name=hope_work_place]").before("<div><span id=\"addHAID_"+addHACount+"\" class=\"ha_del\" onclick=\"ha_del(this);\">×削除</span>"+$("select[name=has_adds] option:selected").text()+" "+$("select[name=has_add_sub] option:selected").text()+"</div>");
		$("#addHAID_"+addHACount).parent().addClass("addHopeList");
		$("input[name=hope_work_place]").val(hope_work_place.join("/"));
		$("select[name=has_adds]").val("");
		$("select[name=has_add_sub]").val("");
		$(this).attr("disabled","disabled").val("希望勤務地を追加");
		$(this).after('<span class="hint">希望勤務地を追加しました。<br />さらに追加するには再び都道府県から選択して下さい</span>');
		$("#drawhope_work_placeSelecter span.hint").delay(3000).fadeOut("slow",function(){$(this).remove();});

		addHACount++;
	});

});
function ha_init(){
	addHACount=1;
	$("span.ha_del").each(function(){
		$(this).attr("id","addHAID_"+addHACount);
		addHACount++;
	});

}
function ha_del(e){
	var delId=parseInt($(e).attr("id").replace("addHAID_",""))-1;
	var hope_work_place=$("input[name=hope_work_place]").val().split("/");
	hope_work_place.splice(delId,1);
	$("input[name=hope_work_place]").val(hope_work_place.join("/"));
	addHACount=1;
	$(e).parent().fadeOut("",function(){
		$(e).parent().remove();
		$("span.ha_del").each(function(){
			$(this).attr("id","addHAID_"+addHACount);
			addHACount++;
		});
	});
}
function able2add_ha(){
	$("input[name=add_ha]").removeAttr("disabled");
}

function togglePublish( userID )
{
	if(confirm("公開する履歴書を変更しますか？")){
		var id = $("input[name='id']:checked").val();
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'c=resumeApi&m=togglePublish&id=' + id + '&user_id=' + userID
		})
		.done(function( $res ){ location.reload(); })
		.fail(function( $xml , $status , $e ){alert("切り替え処理に失敗しました。")});
	}
}
