// jQuerySteps
function init_steps() {
	$("#charges_steps").steps({
		headerTag: "h3",
		bodyTag: "section",
		transitionEffect: "slideLeft",
		autoFocus: true,
		labels: {
			next: "次へ", 
			previous: "戻る", 
			finish: "確認する"
		},
		onFinished: function() {
			document.forms["sys_form"].submit();
		},
	});
}

// 併用設定の初期化
function init_combination() {
	if($('[name=user_limit]').val()=="off" || ($('[name=apply]').val()=="off" && $('[name=employment]').val()=="off")) {
		$(".combination_check").hide();
		
	} else {
		$(".combination_check").show();
	}
	
	toggle_combination();
}

// 併用設定が不要な場合、確認画面から隠す
function toggle_combination() {
	if($("input[name=user_limit]:checked").val()=="off" || ($("input[name=apply]:checked").val()=="off" && $("input[name=employment]:checked").val()=="off")) {
		$(".combination").hide();
	} else {
		$(".combination").show();
	}
}

// お祝い金チェックボックスの表示・非表示について
function toggle_gift() {
	// 応募課金チェック
	if($("input[name=apply]:checked").val()=="on") {
		$("input[value=apply]").parent().show();
	} else {
		$("input[value=apply]").prop('checked', false);
		$("input[value=apply]").parent().hide();
	}
	
	// 採用課金チェック
	if($("input[name=employment]:checked").val()=="on") {
		$("input[value=employment]").parent().show();
	} else {
		$("input[value=employment]").prop('checked', false);
		$("input[value=employment]").parent().hide();
	}
	
	// 利用期間課金チェック
	if($("input[name=user_limit]:checked").val()=="on") {
		if($("input[name=apply]:checked").val()=="off" && $("input[name=employment]:checked").val()=="off") {
			$("input[value=user_limit]").parent().show();
		} else if($("input[name=plan_select]:checked").val()=="on") {
			$("input[value=user_limit]").parent().show();
		} else {
			$("input[value=user_limit]").prop('checked', false);
			$("input[value=user_limit]").parent().hide();
		}
	} else {
		$("input[value=user_limit]").prop('checked', false);
		$("input[value=user_limit]").parent().hide();
	}
	
	// 全部offのときお祝い金項目を消す
	if($("input[name=apply]:checked").val()=="off" && $("input[name=employment]:checked").val()=="off" && $("input[name=user_limit]:checked").val()=="off") {
		$(".gift_checkbox").hide();
	} else {
		$(".gift_checkbox").show();
	}
}