var totalAddCount=0;
var addLoopCount=0;

	function specialSetting(elm,type){

		var id = new Array;
		$("input:checked",elm).map(function()
			{ id.push($(this).val()); });

		if(!id.join("/")){
			alert('実行対象を１つ以上チェックして下さい。');
			return;
		}

		var flag = confirm( '指定の処理を実行します｡' );
		if(!flag) return;

		var val =  $("select[name='special_chk']").val();
		specialInsertProc(elm,type,val);

	}

	function specialDeleteAll(e,type,val,max){

		var flag = confirm( '指定の処理を実行します｡' );
		if(!flag) return;
		$(e).val("削除しています...").attr("disabled","disabled");
		specialDeleteAllProc(type,val,max);

	}

	function specialDeleteAllProc(type,val,max){
		var param = "val="+val;
		/*
		connectAPI4SP_ALL(type,"deleteSpecialAll",param);
		*/
		
		var requestCount=max;
		var maxDoCount=100;
		
		var splitCount=Math.ceil(requestCount/maxDoCount);
		
		
		doDeleteQuery(type,val,0,maxDoCount,splitCount,max);
		
		$('html,body').animate({ scrollTop: 0 });
	
	}

	function connectAPI4SP_ALL($iClass_, $iMethod_, $iParam_ ){

		$iParam_ += '&c=specialSettingApi&type='+$iClass_;

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'm=' + $iMethod_ + '&' + $iParam_
		})
		.done(function( res ){
			alert('すべての求人情報を特集ページから削除しました。');
			location.href="index.php?app_controller=page&p="+getGetParam()["p"];
		})
		.fail(function( xml , status , e ) { alert( '通信に失敗しました。' ); });
	}

	function specialDelete(elm,type,val){

		var id = new Array;
		$("input:checked",elm).map(function()
			{ id.push($(this).val()); });

		if(!id.join("/")){
			alert('実行対象を１つ以上チェックして下さい。');
			return;
		}

		var flag = confirm( '指定の処理を実行します｡' );
		if(!flag) return;

		specialDeleteProc(elm,type,val);

	}

	function specialDeleteProc(elm,type,val){
		var param = "val="+val;
		connectAPI4SP(elm,type,"deleteSpecial",param);
	}

	function specialInsertProc(elm,type,val){
		var param = "val="+val;
		connectAPI4SP(elm,type,"changeSpecial",param);
	}

	function connectAPI4SP($iElement, $iClass_, $iMethod_, $iParam_ ){

		var id = new Array;
		$("input:checked",$iElement).map(function()
			{ id.push($(this).val()); });

		$iParam_ += '&c=specialSettingApi&type='+$iClass_+'&id='+encodeURIComponent(id.join("/"));

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'm=' + $iMethod_ + '&' + $iParam_
		})
		.done(function( res ){
			if($iMethod_!="deleteSpecial"){
				alert('特集ページに求人情報を追加しました。\n特集ページへ移動します。');
				location.href=res;
			}else{
				alert('チェックした求人情報を特集ページから削除しました。');
				location.href="index.php?app_controller=page&p="+getGetParam()["p"];
			}
		})
		.fail(function( xml , status , e ) { alert( '通信に失敗しました。' ); });
	}
	
	function doDeleteQuery(type,pid,start,limit,splitCount,max){
		if(totalAddCount==0 && $("#special_progress").html()==null){
			$("#job_special").before("<div id=\"special_progress\"><img src=\"./common/img/system/ajax_loading_black.gif\" style=\"vertical-align:middle;\" /> "+max+"件の削除処理を実行中です...<br /><p class=\"progress_area\"><span id=\"now_progress\"></span></p></div>");
			$("#now_progress").css({"display":"block","text-align":"right","min-width":"100px","width":"1%","color":"#fff","background-color":"#0096d4"});
			$("#special_progress > p.progress_area").css({"background-color":"#efefef","margin":"10px 0px"});
		}
		
		var $iParam_ = '&c=specialSettingApi&type='+type+'&pid='+pid+'&start='+start+'&limit='+limit;

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'm=splitDelete&' + $iParam_
		})
		.done(function( res ){
			addLoopCount++;
			totalAddCount=totalAddCount+parseInt(res);

			$("#special_progress > p > #now_progress").html("<span style=\"display:block; margin:5px;\">"+((100/splitCount)*addLoopCount).toFixed(2)+"％完了</span>").animate({ "width":((100/splitCount)*addLoopCount).toFixed(2)+"%" },1000);

			if(max<=totalAddCount){
				location.href="index.php?app_controller=page&p="+getGetParam()["p"];
			}else{
				doDeleteQuery(type,pid,start,limit,splitCount,max);
			}
		})
		.fail(function( xml , status , e ) { alert( '通信に失敗しました。' ); });
	}
	
	function doUpdateQuery(type,pid,start,limit,splitCount){
		if(totalAddCount==0 && $("#special_progress").html()==null){
			$("#job_special").before("<div id=\"special_progress\"><img src=\"./common/img/system/ajax_loading_black.gif\" style=\"vertical-align:middle;\" /> "+getGetParam()['add']+"件の追加処理を実行中です...<br /><p class=\"progress_area\"><span id=\"now_progress\"></span></p></div>");
			$("#now_progress").css({"display":"block","text-align":"right","min-width":"100px","width":"1%","color":"#fff","background-color":"#0096d4"});
			$("#special_progress > p.progress_area").css({"background-color":"#efefef","margin":"10px 0px"});
		}
		
		var $iParam_ = '&c=specialSettingApi&type='+type+'&pid='+pid+'&start='+start+'&limit='+limit;

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'm=splitUpdate&' + $iParam_
		})
		.done(function( res ){
			addLoopCount++;
			totalAddCount=totalAddCount+parseInt(res);

			$("#special_progress > p > #now_progress").html("<span style=\"display:block; margin:5px;\">"+((100/splitCount)*addLoopCount).toFixed(2)+"％完了</span>").animate({ "width":((100/splitCount)*addLoopCount).toFixed(2)+"%" },1000);

			if(getGetParam()["add"]<=totalAddCount){
				location.href="index.php?app_controller=page&p="+getGetParam()["p"];
			}else{
				doUpdateQuery(type,pid,(addLoopCount*limit),limit,splitCount);
			}
		})
		.fail(function( xml , status , e ) { alert( '通信に失敗しました。' ); });
	}
