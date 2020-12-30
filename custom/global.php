<?PHP

include_once "./include/base/Util.php";

/*******************************************************************************************************
 * <PRE>
 *
 * 汎用関数群
 *
 * @version 1.0.0
 *
 * </PRE>
 *******************************************************************************************************/

class SystemUtil extends SystemUtilBase{

	function getNameList( $type, $idData = "" )
	{
		if( !strlen($idData) ) { return; }

		$idList = explode( "/", $idData );

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'id', 'in', $idList );

		$row = $db->getRow($table);
		$nameList = null;
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$nameList[$db->getData( $rec, 'id' )] = $db->getData( $rec, 'name' );
		}

		return $nameList;
	}

	/**
	 * unixtimeを丸める
	 *
	 * @param t umixtime
	 * @param f 丸めるフォーマット
	 * @return unitime
	 */
	function createEpochTime( $t, $f ){
		switch( $f ){
			case 'now': case 'n':
				break;
			case 'monthtop': case 'mt':
				$t = mktime( 0, 0, 0, date("m",$t)  , 1, date("Y",$t));
				break;
			case 'monthend': case 'me':
				$t = mktime( 0, 0,-1, date("m",$t)+1  , 1, date("Y",$t));
				break;
			case 'premonthtop': case 'mt-1':
				$t = mktime( 0, 0, 0, date("m",$t)-1  , 1, date("Y",$t));
				break;
			case 'premonthend': case 'me-1':
				$t = mktime( 0, 0,-1, date("m",$t)  , 1, date("Y",$t));
				break;
			case 'daytop': case 'dt':
				$t = mktime( 0, 0, 0, date("m",$t) , date("d",$t) , date("Y",$t));
				break;
			case 'dayend': case 'de':
				$t = mktime( 0, 0, -1, date("m",$t)  ,date("d",$t)+1, date("Y",$t));
				break;
			default:
				break;
		}
		return $t;
	}

	//指定の年度を取得
	static function getYearly($label = null){

		switch($label){
			case "afterNext":	$add = 2;	break;
			case "next":		$add = 1;	break;
			case "now":
			default:
								$add = 0;	break;
			case "prev":		$add = -1;	break;
			case "beforePrev":	$add = -2;	break;
		}

		$y = (int)date("Y");
		$m = (int)date("m");
		switch($m){
			case 1:
			case 2:
			case 3:
				return $y+$add;
				break;
			default:
				return $y+$add+1;
				break;
		}
	}

	/*
	 * idから求人種別を判断する
	 */
	static function getJobType($id){
		if(substr($id, 0,2) == "JN")
			return "fresh";
		elseif(substr($id, 0,1) == "J")
			return "mid";

		return null;
	}

	static function getUserType($userID){
		if(substr($userID, 0,2) == "NB")
			return "nobody";
		elseif(substr($userID, 0,1) == "N")
			return "nUser";
		elseif(substr($userID, 0,1) == "C")
			return "cUser";

		return null;
	}

	/**
	 * 非同期処理をapi経由で実行する時のラッパー関数
	 *
	 * @param class クラス名
	 * @param method 関数名
	 * @param param その他パラメータ
	 * @return true/false
	 */
	function async( $class, $method, $param = Array() )
	{
		global $HOME;

		$url          = $HOME . 'api.php';
		$param[ 'c' ] = $class;
		$param[ 'm' ] = $method;

		return self::request( $url, $param );
	}

	/**
	 * 自動出力フォームのaction属性に設定するべき値を判断して取得する。
	 *
	 * @param  iMode フォームの種類。
	 * @return action属性の値。
	 */
	function GetFormTarget( $iMode )  //
	{
		switch( WS_SYSTEM_SYSTEM_FORM_ACTON ) //遷移方法の種類で分岐
		{
			case 'normal' : //通常
			{
				switch( $iMode ) //フォームの種類で分岐
				{
					case 'registForm' : //登録フォーム
						{ return 'regist.php?type=' . $_GET[ 'type' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'registCheck' : //登録確認フォーム
						{ return 'regist.php?type=' . $_GET[ 'type' ] . '&' . WS_SYSTEM_SYSTEM_FORM_CHECK_LABEL; }

					case 'editForm' : //編集フォーム
						{ return 'edit.php?&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'editCheck' : //編集確認フォーム
						{ return 'edit.php?&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_CHECK_LABEL; }

					case 'deleteForm' : //削除フォーム
						{ return 'delete.php?&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'deleteCheck' : //削除確認フォーム
						{ return 'delete.php?&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_CHECK_LABEL; }

					case 'restoreForm' : //復元フォーム
						{ return 'restore.php?&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'infoPage' : //詳細画面フォーム
						{ return 'info.php?&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }
				}
			}

			case 'index' : //indexにコントローラ名を指定(MVC動作用)
			{
				switch( $iMode ) //フォームの種類で分岐
				{
					case 'registForm' : //登録フォーム
						{ return 'index.php?app_controller=register&type=' . $_GET[ 'type' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'registCheck' : //登録確認フォーム
						{ return 'index.php?app_controller=register&type=' . $_GET[ 'type' ] . '&' . WS_SYSTEM_SYSTEM_FORM_CHECK_LABEL; }

					case 'editForm' : //編集フォーム
						{ return 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'editCheck' : //編集確認フォーム
						{ return 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_CHECK_LABEL; }

					case 'deleteForm' : //削除フォーム
						{ return 'index.php?app_controller=delete&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'deleteCheck' : //削除確認フォーム
						{ return 'index.php?app_controller=delete&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_CHECK_LABEL; }

					case 'restoreForm' : //復元フォーム
						{ return 'index.php?app_controller=restore&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }

					case 'infoPage' : //詳細画面フォーム
						{ return 'index.php?app_controller=info&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ] . '&' . WS_SYSTEM_SYSTEM_FORM_INPUT_LABEL; }
				}
			}

			case 'null' : //空にする
				{ return null; }
		}
	}

	/**
	 * @brief デバッグ出力用 変数の中身を出力する
	 * @param $obj object
	 * @param $file string 出力先のファイルパス
	 * @return void
	 */
	static function dumper($obj, $file='./logs/datacheck.txt')
	{
		if( !file_exists($file) ){ touch($file); chmod($file, 0666); }
		ob_start();
		echo "------". date("Y_m_d_H_i_s")."-------\n";
		var_dump($obj);
		echo "-------------\n\n";
		$ret = ob_get_contents();
		ob_end_clean();
		error_log($ret, 3, $file);
	}


	/**
	 * 静的URL文字列を返す
	 * @param array|string $typeID 職種、勤務形態、特徴、都道府県のID
	 * @param string $type 求人タイプ mid or fresh
	 * @param string $controller コントローラー search or info
	 * @return string ハイフンで連結された静的URL
	 */
	public static function getStaticURL($id, $type, $controller = 'search') {
		$res = '';
		$list = array(); 
		switch($type) {
			case 'cUser':
				$list[] = 'user';
				break;
			default:
				$list[] = $type;
		}

		switch($controller) {
			case 'search':
				break;
			case 'info':
			default:
				$list[] = $controller;
		}

        if(is_array($id)) {
            if(count($id)) {
				foreach($id as $val) {
					$list[] = $val;
				}
            } else {
				throw new LogicException('正しくIDが指定されていません。');
			}
        } else if(strlen($id)) {
			$list[] = $id;
        } else {
			throw new LogicException('正しくIDが指定されていません。');
		}
        return implode('-', $list);
	}
	
	/**
	 * パラメータに$checkNameで与えたパラメータのみが存在しているかどうかチェック
	 * @param array $param チェック対象パラメータ
	 * @param array $checkName チェックするパラメータ名
	 * @param array $unsetList パラメータから除去するパラメータ名
	 * @param array &$valList チェックしたパラメータ名のみが存在していた場合、そのパラメータの値が配列で入る
	 * @return boolean $checkNameで与えたパラメータ名のみが存在していた場合true
	 */
	public static function checkOnlySearch(array $param, array $checkName, array $unsetList = array(), &$valList = array()) 
	{
		$param = SystemUtil::arrayOmit($param);
		$unsetList = array_merge(array('app_controller', 'run', 'type'), $unsetList);
		$jobType = $param['type'];
		foreach($unsetList as $name) {
			if(isset($param[$name])) {
				unset($param[$name]);
			}
		}
		//パラメータのPALを除去
		foreach($param as $key => $val) {
			if(strpos($key, '_PAL') !== false) {
				unset($param[$key]);
			}
		}

		foreach($checkName as $names) {
			$res = array();
			$p = $param;
			foreach($names as $name) {
				$flg = false;
				if(isset($p[$name])) {
					$v = $p[$name];

					if(is_array($v)) {
						$v = array_shift($v);
						if(strlen($v)) {
							$flg = true;
							$res[$name] = $v;
							unset($p[$name]);
						}
					} else if(strlen($v)) {
						$flg = true;
						$res[$name] = $v;
						unset($p[$name]);
					}
				}

				if(!$flg) {
					break;
				}
			}

			//ページナンバーが存在した場合
			if(isset($p['page'])) {
				$page = $p['page'];
				if($page != 0) {
					$res['page'] = $page;
				}
				unset($p['page']);
			}

			if(!count($p) && count($res)) {
				$valList = $res;
				return true;
			}
		}
		return false;
	}
}
