function historyAllDelete(mode){
	var flag = confirm( '最近見た求人履歴をすべて削除します。\nよろしいですか？' );
	if(flag)
	{
	$.cookie(mode,null);
	location.reload();
	}
}
function checkedItems(mode){

	if($.cookie(mode)){
		var script=document.createElement("script");
		script.src="jsonp.php?type="+mode+"&id="+$.cookie(mode).split(",");
		document.body.appendChild(script);
	}else{
		$("div.checkeditems > ul.itemDataList").append("<li>最近見た求人はありません。</li>");
	}
}
function midData(json){
	drawJobHistoryData("mid",json["Body"]);
}

function freshData(json){
	drawJobHistoryData("fresh",json["Body"]);
}

function drawJobHistoryData(mode,itemsData){
	
	for(var i=0;i<itemsData.length&&i<5;i++){

		var ts = itemsData[i].regist;
		var d = new Date( ts * 1000 );
		var year  = d.getFullYear();
		var month = d.getMonth() + 1;
		var day  = d.getDate();
		var hour = ( d.getHours()   < 10 ) ? '0' + d.getHours()   : d.getHours();
		var min  = ( d.getMinutes() < 10 ) ? '0' + d.getMinutes() : d.getMinutes();
		var sec   = ( d.getSeconds() < 10 ) ? '0' + d.getSeconds() : d.getSeconds();
		
		var work_place_adds = itemsData[i].work_place_adds == null ? "未設定" : itemsData[i].work_place_adds;

		if(itemsData[i].image1){
			var image='<img src="thumb.php?src='+itemsData[i].image1+'&width=133&height=100" />';
		}else{
			var image='<img src="common/img/noimage.gif" />';
		}

		var html="";
		html='<li><p class="img"><a href="index.php?app_controller=info&type='+mode+'&id='+itemsData[i].id+'">'+image+'</a></p>'
		+'<p class="cate">'+itemsData[i].category+'</p>'
		+'<h3 class="title"><a href="index.php?app_controller=info&type='+mode+'&id='+itemsData[i].id+'">'+itemsData[i].name+'</a><span>（'+work_place_adds+'）</span></h3></li>';
		$("div.checkeditems > ul.itemDataList").append(html);
	}

	$("#historyCount").text(itemsData.length);
}
