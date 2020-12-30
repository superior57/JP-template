function deleteMessage(tid){

	if(confirm("このユーザーとのやりとりを全て削除します。")){
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "html",
			data: "c=messageApi&m=delete&tid=" + tid
		})
		.done(function(res){
			switch(res){
			case "contractExpire":
				alert("メッセージの送受信サービスをご利用頂くには、有料サービスへのご契約が別途必要となります。");
				break;
			case "notOwner":
				alert("不正な操作です｡");
				break;
			default:
				location.reload();
			}
		})
		.fail(function(xml, status, e){
			alert("不正な操作です。")
		});
	}
}

function declinationScout(tid,jobID){
    if(confirm("スカウトを辞退します。よろしいですか？\n(同一求人のスカウトを全て辞退します。)")){
        jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "html",
			data: "c=messageApi&m=declinationScout&tid=" + tid + "&jobID=" + jobID
		})
		.done(function(res){
			alert("辞退しました。");
			location.reload();
		})
		.fail(function(xml, status, e){
			alert("不正な操作です。")
		});
    }
}

$(function(){
	if(getGetParam()["app_controller"]=="info" && getGetParam()["type"]=="message"){
		var txt = $("textarea[name=message]").val();
		if(txt){
			$("textarea[name=message]").val(txt.replace(/^(.+?)$/mg,"＞ $1"));
			$("input[name=sub]").val("Re: "+$("input[name=sub]").val());
		}
	}
});
