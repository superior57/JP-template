/*****************************************************************************
 * 募集職種（検索用）の値で募集職種（表示用）を置換
 *****************************************************************************/
function assistJobType() 
{
	var name = "";
	var text = "";
	
	name = "select[name=items_type] option:selected";
	if( $(name).val().length > 0 ) { text += $(name).text(); }

	if( text.length>0 ) { $("input:text[name=items_type_label]").val( text ); }
}

/*****************************************************************************
 * 勤務形態（検索用）の値で勤務形態（表示用）を置換
 *****************************************************************************/
function assistJobForm() 
{
	var name = "select[name=items_form] option:selected";
	if( $(name).val().length > 0 )
		{ $("input:text[name=items_form_label]").val( $(name).text() ); }
}

/*****************************************************************************
 * 給与（検索用）の値で給与（表示用）を置換
 *****************************************************************************/
function assistSalary() 
{
	var name  = "select[name=salary_type] option:selected";
	if( $(name).val().length > 0 )
	{
		var value = $(name).text()+" "+addFigure($("input:text[name=salary]").val())+$("#salary_unit_disp").html();
		$("textarea[name=salary_label]").val( $("textarea[name=salary_label]").val()+value+"\n" ); 
	}
}
function addFigure(str) {
	var num = new String(str).replace(/,/g, "");
	while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
	return num;
}

/*****************************************************************************
 * 勤務地（検索用）の値で勤務地（表示用）を置換
 *****************************************************************************/
function assistArea() 
{
    var f_flg = $("input[name='foreign_flg']:checked").val();

    if(f_flg=="TRUE"){
        var value = $("input:text[name='foreign_address']").val();
        $("textarea[name=work_place_label]").val( $("textarea[name=work_place_label]").val()+value );
    }else if(f_flg=="FALSE"){
        var value = $("select[name=work_place_adds] option:selected").text();
        value += $("select[name=work_place_add_sub] option:selected").text();
        value += $("input:text[name=work_place_add_sub2]").val();
        value += $("input:text[name=work_place_add_sub3]").val()+"\n";
        $("textarea[name=work_place_label]").val( $("textarea[name=work_place_label]").val()+value );
    }
}

/*****************************************************************************
 * 最寄駅（検索用）の値で最寄駅（表示用）を置換
 *****************************************************************************/
function assistStation() 
{

	var value = "";
	for( i=1; i<=5; i++ )
	{
		if( $("select[name=traffic"+i+"_line] option:selected").val().length > 0 )
		{
			value += $("select[name=traffic"+i+"_line] option:selected").text();
			if( $("select[name=traffic"+i+"_station] option:selected").val().length > 0 )
			{
				value += " "+$("select[name=traffic"+i+"_station] option:selected").text();
			}
			value += "\n";
		}
	}
	$("textarea[name=transport]").val( $("textarea[name=transport]").val()+value ); 
}

/*****************************************************************************
 * 勤務地の値で面接地を置換
 *****************************************************************************/
function assistInterview() 
{
	colList = new Array( 'zip1', 'zip2', 'add_sub2', 'add_sub3' );
	for( i=0; i<colList.length ; i++  )
	{
		$("input[name=interview_site_"+colList[i]+"]").val( $("input[name="+colList[i]+"]").val() ); 
	}
	
	var add_sub_col = "select[name=interview_site_add_sub]";
	var add_sub_val = $("select[name=add_sub]").val();

	$("select[name=interview_site_adds]").val( $("select[name=adds]").val() );	
	if(! $(add_sub_col+" option[value="+add_sub_val+"]").length)
	{
		if( $.support.noCloneChecked )	 { $("select[name=interview_site_adds]").trigger("change"); }
		else							 { $("select[name=interview_site_adds]")[0].onchange(); }
	}


	var count = 0;
	var time = setInterval( function() {
		if( $(add_sub_col+" option[value="+add_sub_val+"]").length )
		{
			$(add_sub_col).val( add_sub_val );
			count = 30;
		}
			
		if( ++count > 30 ) 	{ clearInterval(time); }
	}, 50 );
	
}

/*****************************************************************************
 * 現在の日付をセットする。
 *
 * @param col 時間をセットするカラム名
 *****************************************************************************/
function setTimeNow( col ) 
{
	var date = new Date();

	$('#'+col+'_start_year'  ).val(date.getFullYear());
	$('#'+col+'_start_month'  ).val(date.getMonth()+1);
	$('#'+col+'_start_day'  ).val(date.getDate());
	
	$('#'+col+'_end_year'  ).val(date.getFullYear());
	$('#'+col+'_end_month'  ).val(date.getMonth()+1);
	$('#'+col+'_end_day'  ).val(date.getDate());
}


/*****************************************************************************
 * 次回更新要諦日に値をセットする。
 *
 * @param part 範囲始め(start)範囲終わり(end)を指定
 * @param add 日数の補正値
 *****************************************************************************/
function setSchedule( part, add ) 
{
	var date = new Date();
	date = new Date(date.getFullYear(), date.getMonth(), date.getDate()+add);

	$('#update_schedule_'+part+'_year'  ).val(date.getFullYear());
	$('#update_schedule_'+part+'_month'  ).val(date.getMonth()+1);
	$('#update_schedule_'+part+'_day'  ).val(date.getDate());
}

/*****************************************************************************
 * 希望勤務地（検索用）の値で希望勤務地（表示用）を置換
 *****************************************************************************/
function assistWorkPlace()
{
    var name = "";
    var text = "";

    $(".addHopeList").each(function(e){
        if($(this).text().length){
            text += $(this).text().replace("×削除","");
            text += "\n";
        }
    })
    if( text.length>0 ) {
        $("textarea[name=hope_work_place_label]").val( text );
    }
}


/***************************************************
 指定エレメントクリックされたらチェックを全解除する

 ***************************************************/
function toggleforeignDisp(){
    var f_flg = $("input[name='foreign_flg']:checked").val();
    if(f_flg=="TRUE"){
        $("div#foreign").show();
        $("div#japan").hide();
        $("div#japan select").val("");
        $("div#japan input:text").val("");
        
        if($('#stn').length) {
            $("#stn").hide();
            $('select[name="work_place_adds"] option:first').prop('selected', true);
            loadAddSub(this,'work_place_adds','work_place_add_sub','add_sub','adds_id','search','','','');
        }
    }else if(f_flg=="FALSE"){
        $("div#japan").show();
        $("div#foreign").hide();
        $("div#foreign input").val("");
        
        if($('#stn').length) {
        	$("#stn").show();
        }
    }else{
        $("div#japan").hide();
        $("div#japan select").val("");
        $("div#japan input:text").val("");
        $("div#foreign").hide();
        $("div#foreign input").val("");
        
        if($('#stn').length) {
            $("#stn").hide();
            $('select[name="work_place_adds"] option:first').prop('selected', true);
            loadAddSub(this,'work_place_adds','work_place_add_sub','add_sub','adds_id','search','','','');
        }
    }
}

/**
 * SPトップページ用の処理
 * @param {*} settings_obj 
 */
function initTopSearch(settings_obj) {
	var settings = {
			areaSelector: '#areaSearchSelectBox',
			categorySelector: '#categorySearchSelectBox',
			keywordSelector: '#keywordSearchInputBox',
			areaLabelSelector: '#searchLabelArea',
			categoryLabelSelector: '#searchLabelCategory',
			keywordLabelSelector: '#searchLabelKeyword',
			selectSelector: '.selectSelector',
			noneClass: 'searchSelectNone',
	}
	$.extend(settings, settings_obj);

	$(settings.areaSelector + " select").change(function() {
		var str;
		if($(this).attr('name') == 'work_place_adds') {
			var ken = $(this).children('option:selected').text().split('(')[0];
			str = '勤務地：' + ken + ' > 未選択';
		} else {
			var ken = $('select[name="work_place_adds"] option:selected').text().split('(')[0];
			var si = $(this).children('option:selected').text().split('(')[0];
			str = '勤務地：' + ken + ' > ' + si;
		}
		$(settings.areaLabelSelector).text(str);
	});
	$(settings.categorySelector + " select").change(function() {
		$(settings.categoryLabelSelector).text('職種：' + $(this).children('option:selected').text().split('(')[0]);
	});
	$(settings.keywordSelector + " input").change(function() {
		$(settings.keywordLabelSelector).text('キーワード：' + $(this).val());
	});
	$(settings.keywordSelector + " input:not([type='hidden'])").val("");
	$(settings.areaSelector + " select").val("");
	$(settings.categorySelector + " select").val("");

	$(settings.selectSelector).click(function() {
		var label = $(this).data('label');
		var selector = $(this).data('selector');
		if(label.length && selector.length) {
			console.log(selector)
			$(selector).removeClass(settings.noneClass);
			$(selector).not('[data-label="' + label + '"]').addClass(settings.noneClass);
		}
		return false;
	});
}