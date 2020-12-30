<?php
$mlt = htmlspecialchars($_GET["mlt"], ENT_COMPAT, "UTF-8");
if($_GET["ac"]=="register" || $_GET["ac"]=="edit"){
?>
var endDateTime=0;
function session_time_init(){
	endDateTime = (new Date).getTime() + (<?php echo $mlt; ?>*1000);
}
$(function() {
	session_time_init();
	$("body").append('<div id="sessionTimeOut"></div>');

	$("#sessionTimeOut").on("click",function(){
		$.get("module/session_keeper/session_start.php",function(data){
			$("#sessionTimeOut").text("セッションを更新しました");
			session_time_init();
		});
	});


	$(".form_table").before(
		"<p class='description'>"+
		"セキュリティ保護の観点から、あと <span class='sk_timelimit'><?php echo $mlt; ?></span> 間、この画面を開いたまま遷移が行われない場合は自動的にログアウトします。<br />"+
		"自動的にログアウトした場合、それまでの作業内容は失われてしまいます。登録・編集作業が長時間になる場合は、予め入力する内容をメモしておく等して下さい。"+
		"</p>"
	);


	countDown();
});
function countDown() {
	var startDateTime = new Date();
	var left = endDateTime - startDateTime;
	var a_day = 24 * 60 * 60 * 1000;
	var h = Math.floor((left / a_day) * 24)
	var m = Math.floor((left % a_day) / (60 * 1000)) % 60
	var s = Math.floor((left % a_day) / 1000) % 60 % 60
	$("#sessionTimeOut").text(h + ':' + m + ':' + s);
	$(".sk_timelimit").text(h + '時間' + m + '分' + s +'秒');


	if((h+m+s)<=0){
		clearTimeout(skcd);
		skLogout();
	}else{
		if(h==0 && m<15){
			$("#sessionTimeOut").css("display","block");
			$("#sessionTimeOut").html(
			                          "現在のページを開いてから一定時間アクセス（画面の遷移）がないため、セキュリティ保護の観点から"+h + ":" + m + ":" + s+"秒後 に自動的にログアウトします。<br />"+
			                          "現在の作業内容を維持したまま続行する場合は、<br />"+
			                          "こちらをクリックしてセッションを更新することで回避できます。"
			                          );
		}
		var skcd = setTimeout("countDown()", 1000);
	}
}

function skLogout() {
	jQuery.ajax({
		url : 'api.php',
		type : 'POST',
		dataType : "html",
		data : "c=session_keeperApi&m=logout"
	}).done(function(res) {
		alert('再ログインしてください');
		window.location.reload(true);

	});
}

<?php
}
?>