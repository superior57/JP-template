/*****************************************************************************
 * elmの値によって表示を切り替える。
 *
 * @param elm 値を取得する要素/基本的にthisを渡す
 *****************************************************************************/
function changeSalaryDisp( elm ) 
{
	switch(elm.value)
	{
	case '日給':
	case '時給':
	default:
		$("#salary_unit_disp").html("円");
		$("#salary_unit_disp2").html("円");
		break;
	case '月給':
	case '年俸':
		$("#salary_unit_disp").html("万円");
		$("#salary_unit_disp2").html("万円");
		break;
	}
}

/*****************************************************************************
 * elmの値によって表示を切り替える。
 *
 * @param elm 値を取得する要素/基本的にthisを渡す
 *****************************************************************************/
function changeSalaryOptionDisp( elm ) 
{
	$("#salary_hour_disp").css('display','none');
	$("#salary_day_disp").css('display','none');
	$("#salary_month_disp").css('display','none');
	$("#salary_year_disp").css('display','none');

	$("#salary_hour").attr("disabled", "disabled");
	$("#salary_day").attr("disabled", "disabled");
	$("#salary_month").attr("disabled", "disabled");
	$("#salary_year").attr("disabled", "disabled");

	switch(elm.value)
	{
	case '時給':
		$("#salary_hour_disp").css('display','inline');
		$("#salary_hour").removeAttr("disabled");
		break;
	case '日給':
		$("#salary_day_disp").css('display','inline');
		$("#salary_day").removeAttr("disabled");
		break;
	case '月給':
		$("#salary_month_disp").css('display','inline');
		$("#salary_month").removeAttr("disabled");
		break;
	case '年俸':
		$("#salary_year_disp").css('display','inline');
		$("#salary_year").removeAttr("disabled");
		break;
	}
}

/*****************************************************************************
 * 求人の公開設定を変更する
 *
 * @id 求人ID
 * @mode on:掲載 off:非掲載
 *****************************************************************************/
function changePublish( id, mode ) 
{
	var message = new Array();
	message["on"] = "この求人を公開に変更しますか？";
	message["off"] = "この求人を一時取りさげに変更しますか？";

	var flag = confirm ( message[mode] );
	if(flag) {
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: "c=JobApi&m=changePublish&id=" + id + "&mode=" + mode
		})
		.done(function(res){ window.location.reload(true); })
		.fail(function(xml, status, e){ });
	}
	return false;
}

/**
 * 希望条件をフォームにセットする
 */
function setHopeCondition(jobType, obj) {

	var cookie = $.cookie('hope_condition_' + jobType);
	if (!cookie)
		return;

	var params = parseHope(cookie);

	// 設定されているchangeイベントの変更
	var work_place_adds_event = $('[name=work_place_adds]').get(0).onchange;
	$('[name=work_place_adds]').get(0).onchange = function(){
		// 勤務地と最寄り駅を連動させないようにする。
		loadAddSub(this,'work_place_adds','work_place_add_sub','add_sub','adds_id','','','','1')
	}

	// ajaxでデータ読み出し成功段階で値をセットする。
	$(document).ajaxSuccess(function(e,xhr,config){
		var data = parseHope(config.data);
		var parent = data['parent'];
		if( parent != 'undefined'){
			switch(data['tableName']){
				case 'add_sub':
					if(params['work_place_adds'] == parent){
						selectHope(obj, params, 'work_place_add_sub', false);
					}
					break;
				case 'line':
					if(params['traffic_adds'] == parent){
						selectHope(obj, params, 'traffic_line', true);
					}
					break;
				case 'station':
					if(params['traffic_line'] == parent){
						selectHope(obj, params, 'traffic_station', true);
					}
					break;
			}
		}
	});
	// すべてのajaxイベント終了後にajaxSuccessハンドラ無効化と、changeイベントを元に戻す
	$(document).ajaxStop(function(e,xhr,config){
		$(document).unbind('ajaxSuccess');
		$('[name=work_place_adds]').get(0).onchange = work_place_adds_event;
	});


	for (key in params) {
		switch ($('[name="' + key + '"]', obj).prop('tagName').toLowerCase()) {
			case 'select':
				switch(key){
					// 次の3つは ajaxSuccess で処理するのでスキップ
					case 'work_place_add_sub':
					case 'traffic_line':
					case 'traffic_station':
						break;
					default:
						selectHope(obj, params, key, true);
						break;
				}
				break;
			case 'input':
				switch ($('[name="' + key + '"]', obj).attr('type').toLowerCase()) {
					case 'checkbox':
					case 'radio':
						checkHope(obj, params, key);
						break;
					case 'text':
					case 'textarea':
						$(obj).find('input[name="' + key + '"]').val(params[key]);
						break;
				}
				break;
		}
	}
	// フォームの開閉辻褄合わせ
	if($('[name="addition[]"]:checked', obj).length>0){
		$('.plus.addition_ui').hide();			//「特徴を設定する」を消す
		$('.addition_ui').not('.plus').show();	//checkboxと「閉じる」を表示
	}
}

/**
 * 希望条件を連想配列にする
 */
function parseHope(cookie) {
	var buf = decodeURIComponent(cookie).split('&');
	var params = new Object();
	for (var i = 0, l = buf.length; i < l; i++) {
		var parts = buf[i].split('=');


		if (!params[parts[0]]) {
			params[parts[0]] = parts[1];
		} else {
			params[parts[0]] += '/' + parts[1];
		}
	}
	return params;
}

/**
 * 希望条件でselectを選択する
 */
function selectHope(obj, params, column) {
	var data = params[column];
	if (!data)
		return;

	var $select = $(obj).find('[name="' + column + '"]');
	$select.val(data);
	$select.change();
}

/**
 * 希望条件でチェックボックス、ラジオボタンを選択する
 */
function checkHope(obj, params, column) {
	var data = params[column];
	if (!data)
		return;

	if (data.indexOf('/') != -1) {
		// 複数チェックした場合
		data = data.split('/');
		for (var i = 0, l = data.length; i < l; i++) {
			$(obj).find('input[name="' + column + '"][value=' + data[i] + ']').prop('checked', true);
		}
	} else {
		$(obj).find('input[name="' + column + '"][value=' + data + ']').prop('checked', true);
	}
	var $check = $(obj).find('[name="' + column + '"]');
	$check.change();
}

/**
 * 検索条件の即時保存
 * @param {type} jobType
 * @param {type} obj
 * @return {undefined}
 */
function saveSearchCondition(jobType,obj){
	query = $(obj).serialize();
	$.cookie('hope_condition_'+jobType, query, { expires: 365 });
	jQuery.ajax({	// ダミー通信
		url         : 'api.php' ,
		type        : 'POST' ,
		data		: 'c=CommonApi&m=embedSearchRow&type=accountLock&embedID=dummy',
		dataType    : 'text' ,
	}).done(function(data, textStatus, jqXHR){
		alert('保存しました');
	}).fail(function(jqXHR, textStatus, errorThrown){
		alert('通信エラーが発生しました。\n接続を確認して、再度保存してみてください');
	}).always(function(datajqXHR, textStatus, jqXHRerrorTHrown){
	});
}

/**
 * 検索条件の読み出し
 * @param {type} jobType
 * @param {type} obj
 * @return {undefined}
 */
function loadSearchCondition(jobType,obj){
	setHopeCondition(jobType,obj);
}

function clearSearchCondition(obj){

	$(obj)
		.find('input, select, textarea')
		.not(':checkbox,:radio,:button, :submit, :reset, :hidden').val('')
		;

	$(obj)
		.find('input:checkbox, input:radio')
		.prop("checked", false)
		.prop("selected", false)
        ;

	var name = '';
	$(obj).find(":radio").each( function(){
		if(name != $(this).prop('name')){
			name = $(this).prop('name');
			radio = $('[name="' + name + '"]', obj);
			$(radio[0]).prop('checked', true);
			$(radio[0]).change();
		}
	})

}
