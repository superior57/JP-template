/*****************************************************************************
 * newsにリンクを貼る際の表示制御
 *
 *****************************************************************************/
function link_to_ref()
{
	switch($('#link_to').val())
	{
	case '0':
		$('#link_type').css('display','none');
		$('#link_text').css('display','none');
		$('#link_url').css('display','none');
		$('#link_message').css('display','none');
		break;
	case '1':
		$('#link_type').css('display','block');
		$('#link_text').css('display','block');
		$('#link_url').css('display','none');
		link_type_ref();
		break;
	case '2':
		$('#link_type').css('display','block');
		$('#link_text').css('display','none');
		$('#link_url').css('display','block');
		link_type_ref();
		break;
	}
}

/*****************************************************************************
 * newsにリンクメッセージの表示制御
 *
 * @param id 商品ID
 *****************************************************************************/
function link_type_ref()
{
	if($('select[name=link_type]').val() == 0)	 { $('#link_message').css('display','block'); }
	else										 { $('#link_message').css('display','none'); }
}