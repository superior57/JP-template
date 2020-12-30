/*****************************************************************************
 * elmの値によって表示を切り替える。
 *
 * @param elm 値を取得する要素/基本的にthisを渡す
 *****************************************************************************/
function changeLinkOptionDisp( elm ) 
{
	$("#link_text_disp").css('display','none');
	$("#link_image_disp").css('display','none');
	$("#link_sort_disp").css('display','none');
	$("#link_terminal_disp").css('display','none');

	switch(elm.value)
	{
	case 'text':
		$("#link_text_disp").css('display','inline');
		$("#link_sort_disp").css('display','inline');
		$("#link_terminal_disp").css('display','inline');
		break;
	case 'image':
		$("#link_image_disp").css('display','inline');
		$("#link_sort_disp").css('display','inline');
		$("#link_terminal_disp").css('display','inline');
		break;
	}
}