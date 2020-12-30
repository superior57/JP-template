<?PHP
include_once './include/base/WebAPIConnection.php';

	/*******************************************************************************************************
	 * <PRE>
	 * 
	 * 住所から緯度・経度を取得するクラス
     *     Google MapsかYahoo地図サービスを利用して緯度・経度を取得します。
     *     利用する場合は「GoogleAPI Key」と「YahooアプリケーションID」のどちらかが必要になります。
     *
     * 外部設定パラメータ(conf等で設定
     *   $WEBAPI_GEOCODEER_SERVICE = "yahoo";//geocoderに使用するサービス
     *   $YAHOO_APP_ID = ""; 
	 * 
	 * @author 吉岡 幸一郎
	 * @version 1.0.0
	 * 
	 * </PRE>
	 *******************************************************************************************************/

class WebAPIMaps extends WebAPIConnection{
    //yahoo|google
    var $service='yahoo';
    var $apiid=null;
    
    var $survey_land = 'jp';//wld
    
    //コンストラクタ
    function WebAPIMaps(){
        global $WEBAPI_GEOCODEER_SERVICE;
        
        if(isset($WEBAPI_GEOCODEER_SERVICE))
            $this->service = $WEBAPI_GEOCODEER_SERVICE;
    }
    
    function getGeocode($address){
        global $YAHOO_APP_ID;
        global $GOOGLE_API_KEY;
        
        switch( $this->service ){
            case 'yahoo':
                $uri = "/LocalSearchService/V1/LocalSearch?appid=";
                $uri .= $YAHOO_APP_ID;
                $uri .= "&p=";
                $uri .= urlencode($address);
                $this->request("api.map.yahoo.co.jp",$uri,'get');
                $xml = $this->getXMLObject();

                if((int)$xml->Count){
                    switch($this->survey_land){
                        case 'jp':
                            if(isset($xml->Item[0]->DatumTky97))
                                return $xml->Item[0]->DatumTky97;
                            break;
                        case 'wld':
                            if(isset($xml->Item[0]->DatumWgs84))
                                return $xml->Item[0]->DatumWgs84;
                            break;
                    }
                }else{
                    return false;
                }
                break;
        }
        return false;
    }
}

?>