//list用

	function setting(elm,type){

		var id = new Array;
		$("input:checked",elm).map(function()
			{ id.push($(this).val()); });

		if(!id.join("/")){
			alert('実行対象を１つ以上チェックして下さい。');
			return;
		}

		var flag = confirm( '指定の処理を実行します。' );
		if(!flag) return;

		var val =  $("select[name='list_id_chk']").val();
		insertProc(elm,type,val);

	}

	function deleteAll(e,type,val){

		var flag = confirm( '指定の処理を実行します。' );
		if(!flag) return;
		$(e).val("削除しています...").attr("disabled","disabled");
		deleteAllProc(type,val);

	}

	function deleteAllProc(type,val){
		var param = "val="+val;
		connectAPI4DM_ALL(type,"deleteAll",param);
	}

	function connectAPI4DM_ALL($iClass_, $iMethod_, $iParam_ ){

		$iParam_ += '&c=listApi&type='+$iClass_;

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'm=' + $iMethod_ + '&' + $iParam_
		})
		.done(function( res ){
			alert('すべてのユーザーをリストページから削除しました。');
			location.reload();
		})
		.fail(function( xml , status , e ) { alert( '通信に失敗しました。' ); });
	}

	function deleteUser(elm,type,val){

		var id = new Array;
		$("input:checked",elm).map(function()
			{ id.push($(this).val()); });

		if(!id.join("/")){
			alert('実行対象を１つ以上チェックして下さい。');
			return;
		}

		var flag = confirm( '指定の処理を実行します。' );
		if(!flag) return;

		deleteProc(elm,type,val);
	}

	function deleteProc(elm,type,val){
		var param = "val="+val;
		connectAPI4DM(elm,type,"deleteUser",param);
	}

	function insertProc(elm,type,val){
		var param = "val="+val;
		connectAPI4DM(elm,type,"change",param);
	}

	function connectAPI4DM($iElement, $iClass_, $iMethod_, $iParam_ ){

		var id = new Array;
		$("input:checked",$iElement).map(function()
			{ id.push($(this).val()); });

		$iParam_ += '&c=listApi&type='+$iClass_+'&id='+encodeURIComponent(id.join("/"));

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: 'text',
			data: 'm=' + $iMethod_ + '&' + $iParam_
		})
		.done(function( res ){
			if($iMethod_!="deleteUser"){
				alert('DMリストにユーザーを追加しました。');
			}else{
				alert('チェックしたユーザーをDMリストから削除しました。');
				location.reload();
			}
		})
		.fail(function( xml , status , e ) { alert( '通信に失敗しました。' ); });
	}


//mailSend用
$.fn.nextSendList = function(options) {
	options = $.extend({addElement:".list_table tr:last"}, options || {});

	this.on("click",function(e){

		var elm = $(this);
		elm.attr("disabled","disabled");

		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "json",
			data: "c=mailSendApi&m=nextList&id=" + options.id + "&usertype=" + options.user_type + "&current=" + options.current
		})
		.done(function(res){
			$(options.addElement).after(res.html);
			if(res.end != true){
				elm.removeAttr("disabled");
				options.current++;
			}else{
				elm.remove();
			}
		})
		.fail(function(xml, status, e){});
	})
}