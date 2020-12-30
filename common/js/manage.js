//$(function() {
//	$("#menu a").each(function(){
//		var menu=encodeURIComponent($(this).attr("href").match(/(type|key)=[^&]{0,}/));
//		var url=encodeURIComponent(location.search.match(/(type|key)=[^&]{0,}/));
//		if(menu==url && $(this).attr("href")!="javascript:void(0);") $(this).css({"color":"#fff","font-weight":"bold","background-color":"#888"});
//		else if($(this).attr("href")=="index.php" && location.search.replace("?","")=="") $(this).css({"color":"#fff","font-weight":"bold","background-color":"#999"});
//	});
//	
//	var viewheight=document.documentElement.clientHeight;
//	$("#menu").after('<div id="overHeightMenu" style="display:none; z-index:70;"><ul></ul></div>');
//	var scroll_y = document.documentElement.scrollTop || document.body.scrollTop;
//	
//	var delCnt=0;
//	$("#menu li").each(function(){
//		var menuheight=$("#menu > ul > li:last-child").offset().top-scroll_y;
//		if((viewheight-menuheight)<180){
//			$("#overHeightMenu ul").append('<li>'+$("#menu > ul > li:last-child").html()+'</li>');
//			$("#menu > ul > li:last-child").remove();
//			++delCnt;
//		}else{
//			if(delCnt>0) $("#menu > ul > li:last-child").after('<li><a href="javascript:void(0);" onclick="mainMenuMore(this);" class="more">もっと表示</a></li>');
//			return false;
//		}
//	});
	
	
//	$("#menu > ul > li.parent_menu").each(function(){
//		$(this).hover(
//			function(){
//				var scroll_y = document.documentElement.scrollTop || document.body.scrollTop;
//				$("> div",this).addClass("addMainMenu").css("top",$(this).offset().top-scroll_y);
//				$("> div",this).stop().show().css('opacity', 0).animate({opacity: 1}, "fast");
//				$("> div",this).hover("",function(){ $(this).hide(); });
//			},
//			function(){
//				$("> div",this).unbind("mouseenter");
//				$("> div",this).unbind("mouseleave");
//				$("> div",this).hide();
//			}
//		);
//	});

	
	
//	var list = $('#overHeightMenu ul > li').toArray().reverse();
//    $('#overHeightMenu ul').empty();
//	$('#overHeightMenu ul').append(list);
//	
//	$(window).scroll(function () {
//		var scroll_y = document.documentElement.scrollTop || document.body.scrollTop;
//		if(scroll_y<50){
//			$("#menu").animate({ width: 130, top: 90},0);
//		}
//		if(scroll_y>50 && $("#menu").css("top")=="90px"){
//			$("#menu").animate({ width: 130, top: 0},0);
//		}
//	});
//	
//	
//});
function mainMenuMore(e){
	var scroll_y = document.documentElement.scrollTop || document.body.scrollTop;
	$("#overHeightMenu").addClass("addMainMenu").css("top",$(e).offset().top-scroll_y);
	$("#overHeightMenu").fadeIn();
	$("#overHeightMenu").hover(
		"",
		function(){
			$("#overHeightMenu").unbind("mouseenter").unbind("mouseleave");
			$("#overHeightMenu").hide();
		}
	);
}