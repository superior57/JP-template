<?php

class interviewSystem extends System
{

    /**
     * 登録前段階処理。
     * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
     *
     * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
     * @param rec フォームのからの入力データを反映したレコードデータ。
     */
    function registProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        global $ACTIVE_NONE;
        global $ACTIVE_ACCEPT;
        // **************************************************************************************
        
        parent::registProc($gm, $rec, $loginUserType, $loginUserRank, $check);
        
        $db = $gm[$_GET['type']]->getDB();
        $db->setData($rec, 'owner', $LOGIN_ID);
        $db->setData($rec, 'activate', $ACTIVE_ACCEPT);
        $db->setData($rec, 'edit', time());
        
        if (Conf::checkData('interview', 'ad_check', 'regist')) {
            $db->setData($rec, 'activate', $ACTIVE_NONE);
        }
    }

    /**
     * 検索処理。
     * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
     *
     * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
     * @param table フォームのからの入力内容に一致するレコードを格納したテーブルデータ。
     */
    function searchProc(&$gm, &$table, $loginUserType, $loginUserRank)
    {
        $type = SearchTableStack::getType();
        $db = $gm[$type]->getDB();
        
        $table = interviewLogic::getTable($db, $table, $_GET); // デフォルト検索条件をセット
    }

    /**
     * 詳細情報が閲覧されたときに表示して良い情報かを返すメソッド。
     * activateカラムや公開可否フラグ、registやupdate等による表示期間の設定、アクセス権限によるフィルタなどを行います。
     *
     * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
     * @param rec アクセスされたレコードデータ。
     * @return 表示して良いかどうかを真偽値で渡す。
     */
    function infoCheck(&$gm, &$rec, $loginUserType, $loginUserRank)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $ACTIVE_ACCEPT;
        global $LOGIN_ID;
        // **************************************************************************************
        
        $db = $gm[$_GET['type']]->getDB();
        $id = $db->getData($rec, "id");
        $owner = $db->getData($rec, "owner");
        
        $result = true;
        switch ($loginUserType) {
            case 'admin':
                break;
            case 'cUser':
                if ($owner != $LOGIN_ID) {
                    if ($db->getData($rec, 'activate') != $ACTIVE_ACCEPT) {
                        $result = false;
                    }
                    Concept::IsTrue(pay_jobLogic::isAvailable($owner, "mid") || pay_jobLogic::isAvailable($owner, "fresh"))->OrThrow("expiredInterview");
                }
                break;
            case 'nUser':
            default:
                if ($db->getData($rec, 'activate') != $ACTIVE_ACCEPT) {
                    $result = false;
                }
                // 求人企業の課金有効判定
                Concept::IsTrue(pay_jobLogic::isAvailable($owner, "mid") || pay_jobLogic::isAvailable($owner, "fresh"))->OrThrow("expiredInterview");
                break;
        }
        return $result;
    }

    /**
     * 編集前段階処理。
     * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
     *
     * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
     * @param rec フォームのからの入力データを反映したレコードデータ。
     */
    function editProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        global $ACTIVE_NONE;
        // **************************************************************************************
        
        parent::editProc($gm, $rec, $loginUserType, $loginUserRank, $check);
        
        $db = $gm[$_GET['type']]->getDB();
        
        if ($loginUserType == 'cUser') {
            if (Conf::checkData('interview', 'ad_check', 'edit')) {
                $db->setData($rec, 'activate', $ACTIVE_NONE);
            }
        }
    }
    
    /**
     * 編集完了処理。
     * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
     *
     * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
     * @param rec フォームのからの入力データを反映したレコードデータ。
     */
    function editComp(&$gm, &$rec, &$oldRec, $loginUserType, $loginUserRank)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $ACTIVE_ACCEPT;
        // **************************************************************************************
        
        $db = $gm[$_GET["type"]]->getDB();
        $old = $db->getData($oldRec, "activate");
        $new = $db->getData($rec, "activate");
        
        if ($old != $new && $new == $ACTIVE_ACCEPT) {
            MailLogic::noticeInterviewActivate($rec);
        }
        
        MailLogic::editNotice( $rec, $_GET['type'] );
        MailLogic::noticeNewPending($rec, $_GET['type'], 'edit');
    }

	/**
	 * 登録処理完了処理。
	 * 登録完了時にメールで内容を通知したい場合などに用います。
	 *
	 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
	 * @param rec レコードデータ。
	 */
	function registComp(&$gm, &$rec, $loginUserType, $loginUserRank)
	{
		MailLogic::noticeNewPending($rec, $_GET['type'], 'regist');
	}

    /**
     * 詳細情報前処理。
     * 簡易情報変更で利用
     *
     * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
     * @param rec アクセスされたレコードデータ。
     */
    function infoProc(&$gm, &$rec, $loginUserType, $loginUserRank)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $ACTIVE_ACCEPT;
        // **************************************************************************************
        
        // 簡易情報変更（情報ページからの内容変更処理）
        if (isset($_POST['post'])) {
            if ($loginUserType == 'admin') {
                $db = $gm[$_GET['type']]->getDB();
                
                for ($i = 0; $i < count($db->colName); $i ++) {
                    if (isset($_POST[$db->colName[$i]])) {
                        switch ($db->colName[$i]) {
                            case "activate":
                                if ($db->getData($rec, $db->colName[$i]) !== $_POST[$db->colName[$i]]) {
                                    if ($_POST["activate"] == $ACTIVE_ACCEPT) {
                                        MailLogic::noticeInterviewActivate($rec);
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                        $db->setData($rec, $db->colName[$i], $_POST[$db->colName[$i]]);
                    }
                }
                $db->updateRecord($rec);
            }
        }
    }
}

?>
