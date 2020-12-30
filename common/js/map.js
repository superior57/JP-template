(function(){
var map = new Array();
var tmpId = null;
var def_zoom = 13;

var def_lat = 35.6586212780812;
if(window.def_lat){ def_lat = window.def_lat; }

var def_lon =  139.745192527771;
if(window.def_lon){ def_lon = window.def_lon; }

var marker = new Array();

/*****************************************************************************
 * マップを表示
 *
 * @param mapId マップを表示する領域のID
 * @param lat 緯度
 * @param lon 経度
 * @param markerFlg true/false 中心にマーカーを表示する場合true
 * @param mode registの場合map移動時にマーカーを中央に再設定
 *****************************************************************************/
window.loadMap = function( mapId, lat, lon, markerFlg, mode )
{
	if( lat == 0 ) { lat = def_lat; }
	if( lon == 0 ) { lon = def_lon; }

	tmpId = mapId;

    var centerLatLang = new google.maps.LatLng(lat, lon);
    var mapOptions = {
	  zoom: def_zoom,
	  center: centerLatLang,
	  mapTypeId: google.maps.MapTypeId.ROADMAP,
	  mapTypeControl: true,
	  scaleControl: true,
	  streetViewControl: true,
	  navigationControl: true,
	  navigationControlOptions: {
	    style: google.maps.NavigationControlStyle.SMALL
	  }
    };

	map[mapId] = new google.maps.Map(document.getElementById(mapId), mapOptions);

	if(markerFlg)
	{// 中心にマーカーを追加
		marker[mapId] = new google.maps.Marker({ position: centerLatLang, map: map[mapId] });
		if( mode == 'regist' ) {
			window.resetMarker = function(){ marker[mapId].setPosition( map[mapId].getCenter() ); }
			google.maps.event.addListener( map[mapId], "dragend" , window.resetMarker );
		}

	}
}

// マーカーの位置を再設定
window.resetMarkerPosition = function(mapId){ marker[mapId].setPosition( map[mapId].getCenter() ); }

/*****************************************************************************
 * ストリートビューを表示
 *
 * @param mapId マップを表示する領域のID
 * @param lat 緯度
 * @param lon 経度
 *****************************************************************************/
window.loadStreet = function( mapId, lat, lon )
{
	if( lat == 0 ) { lat = def_lat; }
	if( lon == 0 ) { lon = def_lon; }

    var centerLatLang = new google.maps.LatLng(lat, lon);
	var streetview_options = {
		      position: centerLatLang,
		      pov: {
			        heading: 0,
			        pitch: 0,
			        zoom: 0
			      }
		    };
	map[mapId] = new google.maps.StreetViewPanorama(document.getElementById(mapId), streetview_options);
	map[tmpId].setStreetView(map[mapId]);
}

/*****************************************************************************
 * マップの中心点を変更。
 *****************************************************************************/
window.moveMap = function( latlng )
{
	if( !latlng )	 { alert( '座標を特定できません。' ); }
	else
	{
		map[tmpId].setCenter( latlng , def_zoom );
		resetMarkerPosition(tmpId);
	}
}

window.moveAdrressMap = function( adds, marker )
{
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({ address: adds }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			for (var i in results) {
		        if (results[i].geometry) {

		          // 緯度経度を取得
		          var latlng = results[i].geometry.location;

		          moveMap(latlng);
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

/*****************************************************************************
 * 市区町村セレクトボックス変更字にマップの中心点を変更。
 *
 * @param adds 都道府県セレクトボックス名
 * @param add_sub 市区町村セレクトボックス名
 *****************************************************************************/
window.seekMapPoint = function( mapId, adds, add_sub)
{
	str  = $("select[name='"+adds+"'] option:selected").text().match("[^\(\)\d(^未選択)]*");
	str += $("select[name='"+add_sub+"'] option:selected").text().match("[^\(\)\d(^未選択)]*");

	tmpId = mapId;
	moveAdrressMap(str);
}

/*****************************************************************************
 * 地図を移動ボタンを押した際にマップの中心点を変更。
 *
 * @param adds 都道府県セレクトボックス名
 * @param add_sub 市区町村セレクトボックス名
 * @param add_sub2 番地テキスト名
 *****************************************************************************/
window.addsToMap = function( mapId, adds,add_sub,add_sub2)
{
	str  = $("select[name='"+adds+"'] option:selected").text().match("[^\(\)\d(^未選択)]*");
	str += $("select[name='"+add_sub+"'] option:selected").text().match("[^\(\)\d(^未選択)]*");
	str += $("input[name='"+add_sub2+"']").val();

	tmpId = mapId;

	moveAdrressMap(str);
}

/*****************************************************************************
 * 地図を移動ボタンを押した際にマップの中心点を変更。
 *
 * @param address 住所文字列
 *****************************************************************************/
window.addressToMap = function( mapId, address)
{
    str  = $("input[name='"+address+"'] ").val();
    tmpId = mapId;
    moveAdrressMap(str);
}


/*****************************************************************************
 * マップの中心点の緯度・経度をセットする。
 *
 * @param lat 緯度をセットするカラムID
 * @param lon 経度をセットするカラムID
 *****************************************************************************/
window.setLatLon = function( mapId, lat, lon)
{
    var center = map[mapId].getCenter();

	$('#'+lat).val(center.lat());
    $('#'+lon).val(center.lng());
}


})();