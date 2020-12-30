(function(){

	function changeStatus(type){
		var id = new Array;
		var method =  $("select[name='method']").val();
		$("input:checked").map(function()
			{ id.push($(this).val()); });

		switch(method){
			case "Unconfirmed":
			case "allowed":
			case "notallowed":
				changeActivate(type,method,id);
				break;
			case "delete":
				deleteData(type,id);
				break;
			default:
				alert("実行内容を選択して下さい");
		}
	}

	function changeActivate(type,method,id){
		jQuery.ajax({
			url : 'api.php' ,
			type : 'POST',
			dataType : "html",
			data : "c=statusChangeApi&m=update&type="+type+"&id="+encodeURIComponent(id.join("/"))+"&column=activate&val="+method})
		.done(function(res){
			location.reload();
		})
		.fail(function(xml, status, e){});
	}

	function deleteData(type,id){
		if(confirm("本当に削除しますか？")){
			jQuery.ajax({
				url : 'api.php' ,
				type : 'POST',
				dataType : "json",
				data : "c=statusChangeApi&m=delete&type="+type+"&id="+encodeURIComponent(id.join("/"))})
			.done(function(res){
				location.reload();
			})
			.fail(function(xml, status, e){});
		}
	}

	window.changeStatus = changeStatus;
})();