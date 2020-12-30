/*****************************************************************************
 * 郵便番号から住所を検索した内容を新規ウインドウで開く。
 *
 * @param zip1 郵便番号1カラム名
 * @param zip2 郵便番号2カラム名
 * @param adds 都道府県カラム名
 * @param add_sub 市区町村カラム名
 * @param add_sub2 番地カラム名
 *****************************************************************************/
function openZipWin( zip1, zip2, adds, add_sub, add_sub2 ){
    var zip_str = $("input:text[name="+zip1+"]").val()+$("input:text[name="+zip2+"]").val();
	if( zip_str.length > 0 )
	{
        window.open("other.php?key=zip&zip="+zip_str+"&adds="+adds+"&add_sub="+add_sub+"&add_sub2="+add_sub2,"住所検索","width=400,height=240,menubar=no,resizable=no");
         $("#"+zip1+"error").html(''); 
    }
	else { $("#"+zip1+"error").html('郵便番号が入力されていません。<br/>' ); }
}

/*****************************************************************************
 * 指定した住所を各カラムに代入する。
 *
 * @param id 郵便番号1カラム名
 * @param adds 都道府県カラム名
 * @param add_sub 市区町村カラム名
 * @param add_sub2 番地カラム名
 *****************************************************************************/
function setZipData( id, adds, add_sub, add_sub2 )
{
    d = window.opener.document;
	
	var add_sub_col = "select[name="+add_sub+"]";
	var add_sub_val = $("#"+id+"_ADD_SUB").val();
	
	$("select[name="+adds+"]", d).val( $("#"+id+"_ADDS").val() );	
	if(! $(add_sub_col+" option[value="+add_sub_val+"]", d).length)
	{
		if( $.support.noCloneChecked )	 { $("select[name="+adds+"]", d).change(); }
		else					 { $("select[name="+adds+"]", d)[0].onchange(); }
	}
	
	$("input:text[name="+add_sub2+"]", d).val( $("#"+id+"_ADD_SUB2").val() );
	
	var count = 0;
	var time = setInterval( function() {
		if($(add_sub_col+" option[value="+add_sub_val+"]", d).length)
		{
			$(add_sub_col, d).val( add_sub_val );
			count = 30;
		}
		
		if( ++count > 30 ) 	{ clearInterval(time); window.close(); }
	}, 50 );
	
}