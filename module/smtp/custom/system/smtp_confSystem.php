<?php
class smtp_confSystem extends System {
	/**
	 * 編集前段階処理。
	 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec フォームのからの入力データを反映したレコードデータ。
	 */
	function editProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
	{
		parent::editProc( $gm, $rec, $loginUserType, $loginUserRank, $check );
		$db	= $gm[ $_GET['type'] ]->getDB();
		$replaceList = array('username', 'host', 'password');
		foreach($replaceList as $col) {
			$db->setData($rec, $col, str_replace(' ', '', $db->getData($rec, $col)));
		}
		if(!$check) {
            $db->setData($rec, 'password', SMTPLogic::encodePassword($db->getData($rec, 'password')));
		}
	}
    
    /**
	 * 編集フォームを描画する。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec 編集対象のレコードデータ
	 * @param loginUserType ログインしているユーザの種別
	 * @param loginUserRank ログインしているユーザの権限
	 */
	function drawEditForm( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$db	 = $gm[ $_GET['type'] ]->getDB();
		if(!isset($_GET['by'])) {
			$_POST['password'] = SMTPLogic::decodePassword($db->getData($rec, 'password'));
		}
		if(empty($db->getData($rec, 'port'))){
			$db->setData($rec, 'port', '25');
			$_POST['port'] = '25';
		}
        
		parent::drawEditForm( $gm, $rec, $loginUserType, $loginUserRank );
	}

	/**
	 * 登録内容確認。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param edit 編集なのか、新規追加なのかを真偽値で渡す。
	 * @return エラーがあるかを真偽値で渡す。
	 */
	function registCheck( &$gm, $edit, $loginUserType, $loginUserRank )
	{
		$check = parent::registCheck($gm, $edit, $loginUserType, $loginUserRank);

		$data = self::$checkData->getData();
		if($check && SystemUtil::convertBool($data['smtp_flg']) && isset($_GET['by']) && $_GET['by'] == 'form') {
			try {
				$design	 = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'], 'SMTP_TEST_MAIL' );
				SMTPLogic::sendTest($design, $gm[$_GET['type']], $data['host'], $data['username'], $data['password'], $data['secure'], $data['port'], $data['test_mail']);
			} catch(Exception $e) {
				self::$checkData->addError( 'test_mail_error', null, 'test_mail');
			}
		}
		// エラー内容取得
		return $check && self::$checkData->getCheck();
	}
}