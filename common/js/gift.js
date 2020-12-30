function entryCheck(){

	$("input[type=submit]").attr("disabled","disabled").val("確認中です...");

	var jobID = $("input[name='job_id']").val();
	var mail = $("input[name='mail']").val();

	if(!jobID){
		$("input[name='job_id']").next().text("求人情報IDが入力されていません。");
	}else{
		$("input[name='job_id']").next().remove();
	}

	if(!mail){
		$("input[name='mail']").next().text("メールアドレスが入力されていません。");
	}else{
		$("input[name='mail']").next().remove();
	}


	if(!jobID || !mail){ $("input[type=submit]").removeAttr("disabled").val("確認する"); return; }

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "text",
		data: "c=nobodyApi&m=checkEntry&jobID=" + jobID + "&mail=" + mail
	})
	.done(function(res){
		switch(res){
			case "applied":
				alert("既に申請済みです｡");
				$("input[type=submit]").removeAttr("disabled").val("確認する");
				break;
			case "notfound":
				alert("該当の応募はありません｡");
				$("input[type=submit]").removeAttr("disabled").val("確認する");
				break;
			default:
				$("input[type=submit]").val("確認済");
				var elm = $("<div>").text("入力されたメールアドレス宛に申請フォームへのURLを記載したメールを送信しました。そちらから申請を行ってください。");
				$("p.button").append(elm);
		}
	})
	.fail(function(xml, status, e){});
}