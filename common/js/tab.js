$(function(){
	$("a.btn_act").on("click",function(){
		var connectCont = $("a.btn_act").index(this);
		var showCont = connectCont+1;
		$('.motion').css({display:'none'});
		$('#tab'+(showCont)).slideDown('normal');

		$('a.btn_act').removeClass('active');
		$(this).addClass('active');
	});
});
