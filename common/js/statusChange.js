(function(){

	function changeStatus(elm,type){
	
		var id = new Array;
		$("input:checked",elm).map(function()
			{ id.push($(this).val()); });
			
		if(!id.join("/")){
			alert('実行対象を１つ以上チェックして下さい。');
			return;
		}
		
		var flag = confirm( '指定の処理を実行します｡' );
		if(!flag) return;

		var val =  $("select[name='method']").val();
		switch(type){
			case "nUser":
			case "cUser":
			case "mid":
			case "fresh":
			case "gift":
			case "review":
			case "press":
			case "violation_report":
			case "interview":
				commonProc(elm,type,val);
				break;
			case "inquiry":
				inquiryProc(elm,type,val);
				break;
			case "entry":
				if(val=="SUCCESS"){
					if(!confirm("「採用決定」に設定後は変更できなくなります。\n進捗を変更してよろしいですか？")){
						location.reload();
						return;
					}
				}
				entryProc(elm,type,val);
				break;
			case "bill":
				billProc(elm,type,val);
				break;
			default:
				alert("実行内容を選択して下さい");
		}
	}

	function commonProc(elm,type,val){
		switch(val){
			case "Unconfirmed":
			case "allowed":
			case "notallowed":
				var param = "val="+val;
				connectAPI(elm,type,"changeActivate",param);
				break;
			case "delete":
				connectAPI(elm,type,"delete");
				break;
		}
	}

	function entryProc(elm,type,val){
		switch(val){
			case "START":
			case "EP001":
			case "EP002":
			case "SUCCESS":
			case "FAILE":
				var param = "val="+val;
				connectAPI(elm,type,"changeProgress",param);
				break;
		}
	}

	function inquiryProc(elm,type,val){
		var param = "val="+val;
		switch(val){
			case "supportedOK":
			case "supportedNG":
				connectAPI(elm,type,"changeSupported",param);
				break;
		}

	}

	function billProc(elm,type,val){
		var param = "val="+val;
		switch(val){
			case "payOK":
			case "payNG":
				connectAPI(elm,type,"changePayment",param);
				break;
			case "payNoticeOK":
			case "payNoticeNG":
				connectAPI(elm,type,"changePaymentNotice",param);
				break;
		}

	}

	function connectAPI($iElement, $iClass_, $iMethod_, $iParam_ ){
        var XHRs = [];
		$("input:checked",$iElement).map(function() {
			var id = $(this).val();
			var promise = new Promise(function (resolve, reject) {
				var $param = $iParam_ + '&c=statusChangeApi&type=' + $iClass_ + '&id=' + encodeURIComponent(id);

				jQuery.ajax({
					url: 'api.php',
					type: 'POST',
					cache: false,
					dataType: 'json',
					data: 'm=' + $iMethod_ + '&' + $param
				})
				.done(function (res) {
					resolve(res);
				})
				.fail(function (xml, status, e) {
					reject(e);
				})
				.always(function (xhr, status) {
				});

			});
			XHRs.push(promise);
		});

		Promise.all(XHRs).then(function(res){
			num = 0;
			$.each(res,function()
			{
				switch(this.result){
					case "success":
						num++;
						break;
					default:
						break;
				}
			});
			if(num){
				alert(num+"件 変更されました。");
				location.reload();
			}else{
				alert("変更されたデータはありません。")
			}
		},function(value){
			alert( '通信に失敗しました。' );
		});
	}

	window.changeStatus = changeStatus;

})();