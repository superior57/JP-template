function bh_common_set(){
	$("table#business_hours td > select").each(function(){
		var type=$(this).attr("name").split("_")[3];
		var time=$(this).attr("name").split("_")[2];
		if($(this).attr("name").match(/start/)=="start"){
			$(this).val($("select[name=start_common_"+time+"_"+type+"]").val());
		}else{
			$(this).val($("select[name=end_common_"+time+"_"+type+"]").val());
		}
	});
}

$(function(){
	$("tr.dotw").hide();
	$("#dotw_common_set :checkbox:checked").each(function(){
		$("."+$(this).val()).show();
	});
	$("#dotw_common_set :checkbox").on("click",function(){
		var dotw=$(this).val();
		$("."+dotw).toggle();
	});
});
