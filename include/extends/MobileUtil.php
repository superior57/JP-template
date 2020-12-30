<?php

class MobileUtil{
	
	static $TYPE_NUM_PC			= 0;
	static $TYPE_NUM_DOCOMO		= 1;
	static $TYPE_NUM_AU			= 2;
	static $TYPE_NUM_SOFTBANK	= 3;
	static $TYPE_NUM_MOBILE_CRAELER	= 9;
	
	static function getTerminal($ip = null){
	
		if(isset($_GET["mobile"])) return self::$TYPE_NUM_DOCOMO;

		//2012年06月21日現在最新ＩＰリスト

		$IP_DOCOMO	= array();
		$IP_AU 		= array();
		$IP_SOFTBANK = array();
		$IP_MOBILE_CRAELER = array();
		
		//2012年06月21日確認(2011年05月更新 サイト側更新
		//http://www.nttdocomo.co.jp/service/developer/make/content/ip/index.html
		$IP_DOCOMO[] = "210.153.84.0/24";
		$IP_DOCOMO[] = "210.136.161.0/24";
		$IP_DOCOMO[] = "210.153.86.0/24";
		$IP_DOCOMO[] = "124.146.174.0/24";
		$IP_DOCOMO[] = "124.146.175.0/24";
		$IP_DOCOMO[] = "202.229.176.0/24";
		$IP_DOCOMO[] = "202.229.177.0/24";
		$IP_DOCOMO[] = "202.229.178.0/24";
		
		$IP_DOCOMO[] = "210.153.87.0/24"; //純正フルブラウザ
		$IP_DOCOMO[] = "203.138.180.0/24"; //メール送信時
		$IP_DOCOMO[] = "203.138.181.0/24"; //メール送信時
		$IP_DOCOMO[] = "203.138.203.0/24"; //メール受信時

		//2011年06月21日更新(4IP追加、2012年05月08日サイト側更新
		//http://www.au.kddi.com/ezfactory/tec/spec/ezsava_ip.html
		$IP_AU[] = "210.230.128.224/28";
		$IP_AU[] = "121.111.227.160/27";
		$IP_AU[] = "219.108.158.0/27";
		$IP_AU[] = "219.125.146.0/28";
		$IP_AU[] = "61.117.2.32/29";
		$IP_AU[] = "61.117.2.40/29";
		$IP_AU[] = "219.108.158.40/29";
		$IP_AU[] = "222.5.62.128/25";
		$IP_AU[] = "59.135.38.128/25";
		$IP_AU[] = "219.108.157.0/25";
		$IP_AU[] = "219.125.145.0/25";
		$IP_AU[] = "121.111.231.0/25";
		$IP_AU[] = "121.111.227.0/25";
		$IP_AU[] = "118.152.214.192/26";
		$IP_AU[] = "118.159.131.0/25";
		$IP_AU[] = "118.159.133.0/25";
		$IP_AU[] = "118.159.132.160/27";
		$IP_AU[] = "111.86.142.0/26";
		$IP_AU[] = "111.86.141.64/26";
		$IP_AU[] = "111.86.141.128/26";
		$IP_AU[] = "111.86.141.192/26";
		$IP_AU[] = "118.159.133.192/26";
		$IP_AU[] = "111.86.143.192/27";
		$IP_AU[] = "111.86.143.224/27";
		$IP_AU[] = "111.86.147.0/27";
		$IP_AU[] = "111.86.142.128/27";
		$IP_AU[] = "111.86.142.160/27";
		$IP_AU[] = "111.86.142.192/27";
		$IP_AU[] = "111.86.142.224/27";
		$IP_AU[] = "111.86.143.0/27";
		$IP_AU[] = "111.86.143.32/27";
		$IP_AU[] = "111.86.147.32/27";
		$IP_AU[] = "111.86.147.64/27";
		$IP_AU[] = "111.86.147.96/27";
		$IP_AU[] = "111.86.147.128/27";
		$IP_AU[] = "111.86.147.160/27";
		$IP_AU[] = "111.86.147.192/27";
		$IP_AU[] = "111.86.147.224/27";
		
		$IP_AU[] = "222.15.68.192/26"; //純正フルブラウザ
		$IP_AU[] = "59.135.39.128/27"; //純正フルブラウザ
		$IP_AU[] = "118.152.214.160/27"; //純正フルブラウザ
		$IP_AU[] = "118.152.214.128/27"; //純正フルブラウザ
		$IP_AU[] = "222.1.136.96/27"; //純正フルブラウザ
		$IP_AU[] = "222.1.136.64/27"; //純正フルブラウザ
		$IP_AU[] = "59.128.128.0/20"; //純正フルブラウザ
		$IP_AU[] = "111.86.140.40/30"; //純正フルブラウザ
		$IP_AU[] = "111.86.140.44/30"; //純正フルブラウザ
		$IP_AU[] = "111.86.140.48/30"; //純正フルブラウザ
		$IP_AU[] = "111.86.140.52/30"; //純正フルブラウザ
		$IP_AU[] = "111.86.140.56/30"; //純正フルブラウザ
		$IP_AU[] = "111.86.140.60/30"; //純正フルブラウザ
		$IP_AU[] = "111.87.241.128/28"; //純正フルブラウザ
		$IP_AU[] = "111.87.241.144/28"; //純正フルブラウザ

		//2012年06月21日確認(サイト側2012年2月14日更新)
		//http://creation.mb.softbank.jp/mc/tech/tech_web/web_ipaddress.html
		$IP_SOFTBANK[] = "123.108.237.0/27";
		$IP_SOFTBANK[] = "202.253.96.224/27";
		$IP_SOFTBANK[] = "210.146.7.192/26";
		$IP_SOFTBANK[] = "123.108.237.112/28";
		$IP_SOFTBANK[] = "123.108.239.224/28";
		$IP_SOFTBANK[] = "202.253.96.144/28";
		$IP_SOFTBANK[] = "202.253.99.144/28";
		$IP_SOFTBANK[] = "210.228.189.188/30";

		$IP_SOFTBANK[] = "123.108.237.224/27"; //純正フルブラウザ
		$IP_SOFTBANK[] = "202.253.96.0/27"; //純正フルブラウザ
		$IP_SOFTBANK[] = "123.108.237.128/28"; //純正フルブラウザ
		$IP_SOFTBANK[] = "123.108.239.240/28"; //純正フルブラウザ
		$IP_SOFTBANK[] = "202.253.96.160/28"; //純正フルブラウザ
		$IP_SOFTBANK[] = "202.253.99.160/28"; //純正フルブラウザ
		$IP_SOFTBANK[] = "210.228.189.196/30"; //純正フルブラウザ

		//Google モバイル検索（2008 年 6 月中旬以降順次）
		$IP_MOBILE_CRAELER[] = "72.14.199.0/25";
		$IP_MOBILE_CRAELER[] = "209.85.238.0/25";
		 
		//Yahoo! (2008年07月15日 情報公開)
		$IP_MOBILE_CRAELER[] = "74.6.0.0/16";
		$IP_MOBILE_CRAELER[] = "124.83.159.146/27";
		$IP_MOBILE_CRAELER[] = "124.83.159.178/29";
		$IP_MOBILE_CRAELER[] = "124.83.159.186";
		$IP_MOBILE_CRAELER[] = "124.83.159.224/28";
		$IP_MOBILE_CRAELER[] = "124.83.159.240/29";
		
		//msn
		$IP_MOBILE_CRAELER[] = "65.52.0.0/14";
		
		//モバイルgoo
		$IP_MOBILE_CRAELER[] = "210.150.10.32/27";
		$IP_MOBILE_CRAELER[] = "203.131.250.0/24";
		
		//Livedoor
		$IP_MOBILE_CRAELER[] = "203.104.254.0/24";
		
		//froute
		$IP_MOBILE_CRAELER[] = "60.43.36.253";
		 
		//moba-crawler （モバゲータウン内の検索サービス）
		$IP_MOBILE_CRAELER[] = "202.238.103.126";
		$IP_MOBILE_CRAELER[] = "202.213.221.97";

		if( isset( $_SERVER["REMOTE_ADDR"] ) ){
		$ip = $_SERVER["REMOTE_ADDR"];
		}
		
		if( is_null( $ip ) || empty( $ip ) ){
			return self::$TYPE_NUM_PC;
		}

		$row = count($IP_DOCOMO);
		for($i = 0; $i < $row; $i++)
		if(self::subNetmask($ip, $IP_DOCOMO[$i])){
			return self::$TYPE_NUM_DOCOMO;
		}

		$row = count($IP_AU);
		for($i = 0; $i < $row; $i++)
		if(self::subNetmask($ip, $IP_AU[$i])){
			return self::$TYPE_NUM_AU;
		}

		$row = count($IP_SOFTBANK);
		for($i = 0; $i < $row; $i++)
		if(self::subNetmask($ip, $IP_SOFTBANK[$i])){
			return self::$TYPE_NUM_SOFTBANK;
		}

		$row = count($IP_MOBILE_CRAELER);
		for($i = 0; $i < $row; $i++)
		if(self::subNetmask($ip, $IP_MOBILE_CRAELER[$i]))
		return self::$TYPE_NUM_MOBILE_CRAELER;

		return self::$TYPE_NUM_PC;
	}

	static function subNetmask($ip, $ipdata){

		$CIDR 			= array();

		$CIDR[32] = "255.255.255.255";
		$CIDR[31] = "255.255.255.254";
		$CIDR[30] = "255.255.255.252";
		$CIDR[29] = "255.255.255.248";
		$CIDR[28] = "255.255.255.240";
		$CIDR[27] = "255.255.255.224";
		$CIDR[26] = "255.255.255.192";
		$CIDR[25] = "255.255.255.128";
		$CIDR[24] = "255.255.255.0";
		$CIDR[23] = "255.255.254.0";
		$CIDR[22] = "255.255.252.0";
		$CIDR[21] = "255.255.248.0";
		$CIDR[20] = "255.255.240.0";
		$CIDR[19] = "255.255.224.0";
		$CIDR[18] = "255.255.192.0";
		$CIDR[17] = "255.255.128.0";
		$CIDR[16] = "255.255.0.0";
		$CIDR[15] = "255.254.0.0";
		$CIDR[14] = "255.252.0.0";
		$CIDR[13] = "255.248.0.0";
		$CIDR[12] = "255.240.0.0";
		$CIDR[11] = "255.224.0.0";
		$CIDR[10] = "255.192.0.0";
		$CIDR[9] = "255.128.0.0";
		$CIDR[8] = "255.0.0.0";
		$CIDR[7] = "254.0.0.0";
		$CIDR[6] = "252.0.0.0";
		$CIDR[5] = "248.0.0.0";
		$CIDR[4] = "240.0.0.0";
		$CIDR[3] = "224.0.0.0";
		$CIDR[2] = "192.0.0.0";
		$CIDR[1] = "128.0.0.0";

		if(strpos($ipdata, "/") === false) return ($ipdata == $ip);
		list($net, $mask) = explode('/', $ipdata);
		$mask = self::ipConversion($CIDR[$mask]);
		if((self::ipConversion($ip) & $mask) == (self::ipConversion($net) & $mask))
		return true;
		else
		return false;
	}

	static function ipConversion($ip){
		$ips = explode('.', $ip);
		return ($ips[0] << 24) | ($ips[1] << 16) | ($ips[2] << 8) | $ips[3];
	}

	static function getMobileID(){
		global $terminal_type;
		global $i_mode_id;
		$UA = $_SERVER['HTTP_USER_AGENT'];

		$MobileInfo = false;

		switch($terminal_type){
			case self::$TYPE_NUM_DOCOMO:
				if( isset($i_mode_id) && $i_mode_id ){
					//i mode id
					if (isset($_SERVER['HTTP_X_DCMGUID']) ) {
						$MobileInfo = $_SERVER['HTTP_X_DCMGUID'];
					}
				}else{
					//個体識別情報
					preg_match("/ser([a-zA-Z0-9]+)/",$UA, $dprg);
					if ( strlen($dprg[1]) === 11 ) {
						$MobileInfo = $dprg[1];
					} elseif ( strlen($dprg[1]) === 15 ) {
						$MobileInfo = $dprg[1];
						preg_match("/icc([a-zA-Z0-9]+)/",$UA, $dpeg);
						if ( strlen($dpeg[1]) === 20 ) {
							$MobileInfo = $dpeg[1];
						} else {
							$MobileInfo = false;
						}
					} else {
						$MobileInfo = false;
					}
				}
				break;
			case self::$TYPE_NUM_SOFTBANK:
				if ( preg_match("/(SN([a-zA-Z0-9]+))/",$UA,$vprg) ) {
					$MobileInfo = $vprg[1];
				} else {
					$MobileInfo = false;
				}
				break;
			case self::$TYPE_NUM_AU:
				$MobileInfo = $_SERVER['HTTP_X_UP_SUBNO'];
				break;
		}

		return $MobileInfo;
	}

	/**
	 *	個体識別番号を保存する
	 *	@param $type	ユーザータイプを指定
	 *	@param $id		ユーザーＩＤを指定
	 *	@param $calam	固体識別番号保持用項目の指定（default -> mobile）
	 */
	function setMobileID( $type, $id, $calam = "mobile")
	{
		$UTN = self::getMobileID();
		if($UTN !== false && $UTN != ""){
			//個体識別番号が送信されている
			$tgm = SystemUtil::getGMforType($type);
			$db = $tgm->getDB();
			$table	 = $db->getTable();
			$table	 = $db->searchTable(  $table, 'id', '=', $id );
			if( method_exists( $db, 'getFirstRecord') ){
				if( $rec = $db->getFirstRecord( $table ) ){
					$db->setData($rec, $calam, $UTN);
					$db->updateRecord( $rec );
					return true;
				}
			}else{
				if($db->getRow( $table ) != 0){
					$rec = $db->getRecord( $table, 0 );
					$db->setData($rec, $calam, $UTN);
					$db->updateRecord( $rec );
					return true;
				}	
			}
		}
		return false;
	}
	
	static function setSessionID(){
		$sid_name = ini_get('session.name');
		if( isset($_GET[$sid_name]) && strlen($_GET[$sid_name]) ){
			session_id($_GET[$sid_name]);
			unset($_GET[$sid_name]);
		}elseif( isset($_POST[$sid_name]) && strlen($_POST[$sid_name]) ){
			session_id($_POST[$sid_name]);
			unset($_POST[$sid_name]);
		}
	}

	//$sidの初期化
	static function reloadSID(){
		global $sid;
		
		$sid_name = ini_get('session.name');
		
		if( strlen( SID ) ){
			$sid = h(SID);
		}else{
			$sid = h($sid_name."=".session_id() );
		}

		if( !ini_get('session.use_cookies') && ini_get('session.use_trans_sid') != '1' || self::checkNoCookie() )
		{
			$output = ob_get_clean();
  			output_reset_rewrite_vars();
	        output_add_rewrite_var($sid_name,h( session_id() ) );
			ob_start();
			print $output;
		}
	}
	
	// docomo i-mode ブラウザ 1.0 をチェック
	static function checkNoCookie()
	{
		global $terminal_type;
		
		if( $terminal_type == MobileUtil::$TYPE_NUM_DOCOMO ){
            $ua = $_SERVER["HTTP_USER_AGENT"];
			if( preg_match('/^DoCoMo\/1\.0/',$ua) )
            {
                return true;
            }
            else if( preg_match('/^DoCoMo\/2\.0[^\(]+\(c100;/',$ua) )
            {
                return true;
            }
		}
		return false;
	}
}
?>