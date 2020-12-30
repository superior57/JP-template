$(function(){
	$("input[type=text]").each(function(){
		if($(this).attr("name").match("mail")=="mail"){
			var confirmBody=mail_confirm_body($(this).attr("name"));
			$(this).after(confirmBody);
		}
	})
})

function mail_confirm_body(target){
	var body=' <a href="javascript:void(0);" onclick="mail_confirm(\''+target+'\',this);">受信チェック</a><span class="hint">利用するメールアドレスがシステムからのメッセージ通知を受信できるかチェックするには「受信チェック」をクリックします。</span>';
	return body;
}
function mail_confirm(target,e){
	var mailAddress=$("input[name="+target+"]").val();
	if(!mailAddress){
		alert("メールアドレスが未入力です。");
	}else if(!mailAddress.match(/(?:^([a-z0-9][a-z0-9_\-\.\+]*)@([a-z0-9][a-z0-9\.\-]{0,63}\.(com|org|net|biz|info|name|net|pro|aero|coop|museum|[a-z]{2,}))$)/i)){
		alert("メールアドレスが不正です。");
	}else if(mailAddress){
		if(window.confirm("入力されたメールアドレスが当サイトからのメッセージ通知を受信できるかの確認を行います。\n\n"+mailAddress+" 宛に、\nシステムから受信用のテストメールを送信します、よろしいですか？")){
			$(e).html("送信しています...");
			mail_confirm_send(mailAddress,e);
		}else{
			alert('キャンセルされました。\n\nメールアドレスを変更の際は必ず受信チェックを行うことを推奨します。');
		}
	}
}
function mail_confirm_send(mailAddress,e){
	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "html",
		data: "c=mail_confirmApi&m=confirm&address=" + encodeURIComponent(mailAddress)
	})
	.done(function(res){
		$(e).html("受信チェック");
		alert(
			"チェック用のメッセージを送信しました。\n"+
			"\n"+
			"数分が経過しても受信できない場合は、入力されたメールアドレスが間違っているか、\n"+
			"フリーメールアドレス（Yahoo, hotmail等）をご利用の場合\n"+
			"当サービスからのメールが迷惑メールフォルダに自動的に移動されてしまうことがあります。\n"+
			"迷惑メールフォルダをご確認いただくか、受信できるように設定を変更\n"+
			"または、別のメールアドレスで試してみる等、お願い致します。 "
			);
	})
	.fail(function(xml, status, e){
		$(e).html("受信チェック");
		alert("失敗しました");
	});
}