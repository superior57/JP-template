
/*****************************************************************************
 * 都道府県の値から市区町村カラムの要素を変更する。指定されている場合路線も変更。
 *
 * @param elm フォームデータ
 * @param colName 親IDが格納されているカラム
 * @param childCol 子要素のカラム
 * @param childTableName 子要素のテーブル名
 * @param childSearchCol 子要素テーブルの親IDが格納されているカラム名
 * @param lineMode 路線の切替
 * @param countFlg 物件件数表示フラグ
 * @param noneFlg 未選択非表示フラグ
 * @param dispFlg 求人非表示フラグ
 *****************************************************************************/
function loadAddSub(elm, colName, childCol, childTableName, childSearchCol, lineMode, countFlg, noneFlg, dispFlg )
{
  var form = elm.form;
  elm.blur();
  
  var parent;
  parent = $("option:selected",elm).val();
  
  var searchParam = '';
  if( countFlg.length )
  {
	var getParam = getGetParam();
	if( getParam['type'] == undefined ) { getParam['type'] = $('input:hidden[name=type]').val(); }
	searchParam = '&countCol=add_sub&type='+getParam['type'];
  }

  jQuery.ajax({
	url : 'api.php' ,
	type : 'POST',
	dataType : "json",
	scriptCharset : 'UTF-8',
	data : "c=areaApi&m=getChildJsonData&parent="+parent+"&tableName="+childTableName+"&parentCol="+childSearchCol+"&noneFlg="+noneFlg+"&dispFlg="+dispFlg+searchParam
  })
	.done(function(res){
		$(form[childCol]).removeOption(/./);
		$(form[childCol]).addOption(res, false);
		if( noneFlg.length ) { $(form[childCol]).removeOption(0); }
	})
	.fail(function(xml, status, e){
		$(form[childCol]).removeOption(/./);
		if( noneFlg.length ) { $(form[childCol]).removeOption(0); }
	});
  
  switch(lineMode)
  {
  case "regist" :
  case "search" :
  	if($(elm).attr("name").indexOf("traffic") === 0){
  		loadLineEx( elm, parent, lineMode );
	}else{
		$("[name='"+colName+"Ex']").val(parent);
		loadLine( elm, parent, lineMode );
	}
	break;
  }
  
}


/*****************************************************************************
 * 都道府県の値から路線・駅カラムの要素を変更する。
 *
 * @param form フォームデータ
 * @param pref 親ID
 * @param mode フォームモード
 *****************************************************************************/
function loadLine( elm, pref, mode )
{  
  var form = elm.form;
  var searchParam = '';
  
  jQuery.ajax({
	url : 'api.php' ,
	type : 'POST',
	dataType : "json",
	data : "c=CommonApi&m=getChildJsonData&parent="+pref+"&tableName=line&parentCol=adds_ids"+searchParam})
	  .done(function(res){
		switch(mode)
		{
		case 'regist':
		  for( i =1; i<=5; i++ )
		  {
			  $(form['traffic'+i+'_adds']).val(pref);
			  $(form['traffic'+i+'_line']).removeOption(/./);
			  $(form['traffic'+i+'_line']).addOption(res, false);
			  $(form['traffic'+i+'_station']).removeOption(/./);
		  }
		  break;
		case 'search':
		  $(form['traffic_adds']).val(pref);
		  $(form['traffic_line']).removeOption(/./);
		  $(form['traffic_line']).addOption(res, false);
		  
		  $(form['traffic_station']).removeOption(/./);
		  break;
		
		}
	})
	  .fail(function(xml, status, e){
		switch(mode)
		{
		case 'regist':
		  for( i =1; i<=5; i++ )
		  {
			  $(form['traffic'+i+'_line']).removeOption(/./);
			  $(form['traffic'+i+'_station']).removeOption(/./);
		  }
		  break;
		case 'search':
		  $(form['traffic_line']).removeOption(/./);
		  
		  $(form['traffic_station']).removeOption(/./);
		  break;
		
		}
		
	});
}

function loadLineEx( elm, pref, mode )
{  
  var searchParam = '';
  
	jQuery.ajax({
		url : 'api.php' ,
		type : 'POST',
		dataType : "json",
		data : "c=CommonApi&m=getChildJsonData&parent="+pref+"&tableName=line&parentCol=adds_ids"+searchParam
	})
	.done(function(res){
		switch(mode){
			case 'regist':
				$(elm).next().removeOption(/./);
				$(elm).next().addOption(res, false);
				$(elm).next().next().removeOption(/./);
			  break;
			case 'search':
			  $(form['traffic_line']).removeOption(/./);
			  $(form['traffic_line']).addOption(res, false);

			  $(form['traffic_station']).removeOption(/./);
			  break;
		}
	})
	.fail(function(xml, status, e){
		switch(mode){
		case 'regist':
		  for( i =1; i<=5; i++ )
		  {
			  $(form['traffic'+i+'_line']).removeOption(/./);
			  $(form['traffic'+i+'_station']).removeOption(/./);
		  }
		  break;
		case 'search':
		  $(form['traffic_line']).removeOption(/./);

		  $(form['traffic_station']).removeOption(/./);
		  break;
		}
	});

}

/*****************************************************************************
 * 路線の値から駅の要素を変更する。
 *
 * @param elm フォームデータ
 * @param colName 親IDが格納されているカラム
 * @param childCol 子要素のカラム
 * @param childTableName 子要素のテーブル名
 * @param childSearchCol 子要素テーブルの親IDが格納されているカラム名
 * @param prefCol 都道府県要素のカラム
 * @param prefSearchCol 子要素テーブルの都道府県IDが格納されているカラム名
 * @param countFlg 物件数を表示するかどうかのフラグ
 *****************************************************************************/
function loadStation(elm, colName, childCol, childTableName, childSearchCol, prefCol, prefSearchCol, countFlg)
{
  var form = elm.form;
  elm.blur();
  
  var parent = $("[name='"+colName+"'] option:selected").val();
  var pref = $(elm).prev().val();

  var searchParam = '';
  if( countFlg.length )
  {
	var getParam = getGetParam();
	if( getParam['type'] == undefined ) { getParam['type'] = $('input:hidden[name=type]').val(); }
	searchParam = '&countCol=station&type='+getParam['type'];
  }

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "json",
		data: "c=CommonApi&m=getStationJsonData&parent=" + parent + "&tableName=" + childTableName + "&parentCol=" + childSearchCol + "&pref=" + pref + "&prefCol=" + prefSearchCol + searchParam
	})
	.done(function(res){
		$(form[childCol]).removeOption(/./);
		$(form[childCol]).addOption(res, false);
	})
	.fail(function(xml, status, e){
		  $(form[childCol]).removeOption(/./);
	});
}
