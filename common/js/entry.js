function entryStatusChange(e,entry_id){
	
	if($(e).val()=="SUCCESS"){
		if(confirm("「採用決定」に設定後は変更できなくなります。\n進捗を変更してよろしいですか？")){
			$(e).attr("disabled","disabled");
			entryStatusChangeDo($(e).val(),entry_id);
		}else{
			location.reload();
		}
	}else{
		entryStatusChangeDo($(e).val(),entry_id);
	}

}
function entryStatusChangeDo(status,entry_id){

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "html",
		data: "c=entryApi&m=update&id=" + entry_id + "&status=" + status
	})
	.done(function(res){
		if(res == "succeeded"){
			alert("採用が決定したものは変更できません｡")
		}else if(res == "invalid_status"){
			alert("不正なデータです｡")
		}else{
			alert("変更しました");
		}
		location.reload();
	})
	.fail(function(xml, status, e){
		alert("不正な操作です。")
	});
}

function rejectApply(eid){
    if(confirm("応募を不採用とし、求職者へその旨を通知します。")){
        jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "html",
			data: "c=entryApi&m=rejectApply&id=" + eid
		})
		.done(function(res){
			alert("不採用としました。");
			location.reload();
		})
		.fail(function(xml, status, e){
			alert("不正な操作です。")
		});
    }
}

$(function(){
	$("select[name=status] option:selected").each(function(){
		if($(this).val()=="SUCCESS") $(this).parent().attr("disabled","disabled");
	});
});