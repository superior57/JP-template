function initialize(lat,lon) {
	geocoder = new google.maps.Geocoder();
	var mapOptions = {
		center: new google.maps.LatLng(lat,lon),
		zoom: def_zoom,
		mapTypeControl: false,
		scrollwheel: false,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById('map_canvas'),mapOptions);
	
	//イベント登録　地図の表示領域が変更されたらイベントを発生させる
	google.maps.event.addListener(map, 'idle', function(){
		if (!currentInfoWindow) setPointMarker();
	});
	
	google.maps.event.addListener(map, 'dragend', function(){
		if (currentInfoWindow) setPointMarker();
	});
	/*
	google.maps.event.addListener(map, 'zoom_changed', function(){
		setPointMarker();
	});
	*/
}

//XMLで取得した地点を地図上でマーカーに表示
function setPointMarker(){
	//リストの内容を削除
	$('#pointlist > ul').empty();
	$("#pointlist").append('<span><img src="./common/img/system/ajax_loading_black.gif" style="vertical-align:middle;" /> 地点リストを読込中...</span>');
	
	//マーカー削除
	MarkerClear();

	//地図の範囲内を取得
	var bounds = map.getBounds();
	map_ne_lat = bounds.getNorthEast().lat();
	map_sw_lat = bounds.getSouthWest().lat();
	map_ne_lng = bounds.getNorthEast().lng();
	map_sw_lng = bounds.getSouthWest().lng();

	var $form = $('#mapForm');
	var param = '';

	if($form) {
		param = $form.serialize();
	}

	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "xml",
		scriptCharset: 'UTF-8',
		data: 'c=CommonApi&m=getMapData&ne_lat=' + map_ne_lat + '&sw_lat=' + map_sw_lat + '&ne_lng=' + map_ne_lng + '&sw_lng=' + map_sw_lng + '&' + param
	})
	.done(function(xml){
		var i=1;

		//リストの内容を削除
		$('#pointlist > ul').empty();
		$("#pointlist span").remove();

		if(!$(xml).find("Locate").text()){
			$("#foundCounter > span.count").text("0");
			$("#pointlist").append('<span>この地域に該当する情報はありません</span>');
		}else{

			$(xml).find("Locate").each(function(){

				$("#foundCounter > span.count").text(i);

				var LocateLat = $("lat",this).text();
				var LocateLng = $("lng",this).text();
				var LocateId = $("id",this).text();
				var LocateName = $("name",this).text();
				var LocateAddress = $("address",this).text();
				var LocateAddress2 = $("address2",this).text();
				var LocateAddress3 = $("address3",this).text();
				var LocateElements = $("elements",this).text();
				var LocateType = $("type",this).text();
				var html = $("html",this).text();

				MarkerSet(LocateId,LocateLat,LocateLng,LocateName,LocateAddress,LocateAddress2,LocateAddress3,LocateElements,i,LocateType);

				//リスト表示
				var marker_num = marker_ary.length - 1;

				//liタグをセット
				loc = $('<li>').html(html);
				//セットしたタグにイベント「マーカーがクリックされた」をセット
				loc.bind('click', function(){
					google.maps.event.trigger(marker_ary[marker_num], 'click');
					$('html,body').animate({ scrollTop: 0 }, '1');
				});
				//リスト表示
				$('#pointlist > ul').append(loc);
				++i;
			});

		}
	})
	.fail(function(xml, status, e){ });
}


function MarkerSet(id,lat,lng,text,address,address2,address3,elements,num,type){
	var marker_num = marker_ary.length;
	var marker_position = new google.maps.LatLng(lat,lng);
	var markerOpts = {
		map: map,
		icon: 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='+num+'|FF3366|000000',
		position: marker_position
	};
	marker_ary[marker_num] = new google.maps.Marker(markerOpts);

	if(text.length>0){
	
		var infoWndOpts = {
			content : "<div class='infoWnd'><strong><a href='index.php?app_controller=info&type="+type+"&id="+id+"' >"+text+"</a></strong><br /><br />"+address+"<br />"+address2+"<br />"+address3+"<br />"+elements+"</div>"
		};
		var infoWnd = new google.maps.InfoWindow(infoWndOpts);
		google.maps.event.addListener(marker_ary[marker_num], "click", function(){

			//先に開いた情報ウィンドウがあれば、closeする
			if (currentInfoWindow) {
				currentInfoWindow.close();
			}
			//情報ウィンドウを開く
			infoWnd.open(map, marker_ary[marker_num]);
			
			map.panTo(new google.maps.LatLng(lat,lng));
			//開いた情報ウィンドウを記録しておく
			currentInfoWindow = infoWnd;
		});
	}
}


//マーカー削除
function MarkerClear() {
	//表示中のマーカーがあれば削除
	if(marker_ary.length > 0){
		//マーカー削除
		for (i = 0; i <  marker_ary.length; i++) {
			marker_ary[i].setMap();
		}
		//配列削除
		for (i = 0; i <=  marker_ary.length; i++) {
			marker_ary.shift();
		}
	}
}

function moveAdrressMap4Search( adds, marker )
{
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({ address: adds }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			for (var i in results) {
		        if (results[i].geometry) {

		          // 緯度経度を取得
		          var latlng = results[i].geometry.location;

		          moveMap4Search(latlng);
		          break;
		        }
			}
		} else if (status == google.maps.GeocoderStatus.ERROR) {
			alert('座標を特定できません。');
	    } else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
	    	alert('座標を特定できません。');
	    } else {
	    	alert('座標を特定できません。');
	    }
	} );
}

function moveMap4Search( latlng )
{
	if( !latlng ) alert( '座標を特定できません。' );
	else map.setCenter( latlng , def_zoom );
}

function addsToMap4Search( adds,add_sub)
{
	var defaultZoom = {adds:["PF13","PF14","PF23","PF27"]};	//東京,神奈川,愛知,大阪
	var zoomLevel = def_zoom;
	var addsID = $("select[name='"+adds+"'] option:selected").val();

	str  = $("select[name='"+adds+"'] option:selected").text().match("[^\(\)\d(^未選択)]*");
	str += $("select[name='"+add_sub+"'] option:selected").text().match("[^\(\)\d(^未選択)]*");

	//都市部以外は一段階引く
	if($.inArray(addsID,defaultZoom.adds) == -1){
		zoomLevel -= 1;
	}

	map.setZoom(zoomLevel);
	moveAdrressMap4Search(str);
}
