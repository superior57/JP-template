<?php

	class MailLogic
	{

		/**
		 * 同一デザインで複数のアドレスにメールを送信
		 *
		 * @param gm GMオブジェクト
		 * @param rec メールを送信するレコード
		 * @param desgin メールデザイン
		 * @param mailList 送信先アドレスの配列
		 */
		function multiSend($gm, $rec, $design, $mailList)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			if (! is_array($mailList)) {
				$mailList = array($mailList);
			}

			foreach ($mailList as $mail) {
				if (strlen($mail)) {
					Mail::send($design, $MAILSEND_ADDRES, $mail, $gm, $rec, $MAILSEND_NAMES);
				}
			}
		}

		/**
		 * 登録情報編集通知メールを管理者に送信
		 *
		 * @param rec ユーザレコード
		 * @param type ユーザテーブル名
		 */
		function editNotice($rec, $type)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			// 管理者以外が編集した場合のみ通知
			switch ($loginUserType) {
				case 'admin':
					break;
				default:
					$check = false;
					switch ($type) {
						case 'mid':
						case 'fresh':
							$check = Conf::checkData('job', 'edit_notice', 'job');
							break;
						case 'cUser':
						case 'nUser':
							$check = Conf::checkData('user', 'edit_notice', $type);
							break;
						case 'interview':
							$check = Conf::checkData('interview', 'edit_notice', 'on');
							break;
					}

					if ($check) { // 登録情報編集通知を管理者に送信
						$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'EDIT_NOTICE_MAIL');
						Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
					}
					break;
			}
		}

		/**
		 * アクティベートメールを送信
		 *
		 * @param array $rec ユーザーレコード
		 * @param string $type ユーザー種別
		 */
		function activateCheck($rec, $type)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			// アクティベートメールを登録者/管理者に送信
			$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'ACTIVATE_MAIL');

			$mailList[] = $MAILSEND_ADDRES;
			$mailList[] = $db->getData($rec, 'mail');

			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * ユーザーレコードを元にユーザーと管理者にメールを送信する
		 *
		 * @param array $rec ユーザーレコード
		 * @param string $type ユーザー種別
		 * @param string $label 自動メールラベル
		 */
		function sendMailByUserRec($rec, $type, $label)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			$design = Template::getTemplate($loginUserType, $loginUserRank, $type, $label);

			$mailList[] = $MAILSEND_ADDRES;
			$mailList[] = $db->getData($rec, 'mail');

			// cUserの場合サブアドレスにも送信
			if ($type == 'cUser') {
				$subMail = $db->getData($rec, "sub_mail");
				if (strlen($subMail)) {
					$mailList[] = $subMail;
				}
				$subMail2 = $db->getData($rec, 'sub_mail2');
				if (strlen($subMail2)) {
					$mailList[] = $subMail2;
				}
			}

			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * ユーザー登録後、管理者承認があることを企業/求職者と管理者にメールで通知
		 *
		 * @param array $rec ユーザーレコード
		 * @param string $type ユーザー種別
		 */
		function userAdminCheck($rec, $type)
		{
			self::sendMailByUserRec($rec, $type, 'ADMIN_CHECK_MAIL');
		}

		/**
		 * ユーザ情報登録完了メールを送信
		 *
		 * @param rec ユーザレコード
		 * @param type ユーザテーブル名
		 */
		function userRegistComp($rec, $type)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			// ユーザ情報登録完了メールを登録者/管理者に送信
			$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'REGIST_COMP_MAIL');

			$mailList[] = $MAILSEND_ADDRES;
			$mailList[] = $db->getData($rec, 'mail');

			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * 退会処理完了時に、企業/求職者と管理者にメールで通知
		 *
		 * @param array rec ユーザレコード
		 * @param string type ユーザ種別
		 */
		function userDeleteComp($rec, $type)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'DELETE_COMP_MAIL');

			$mailList[] = $MAILSEND_ADDRES;
			$mailList[] = $db->getData($rec, 'mail');

			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * お問い合わせメールを送信
		 *
		 * @param rec お問い合わせレコード。
		 */
		function inquiry( $rec )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM('inquiry');
			$db = $gm->getDB();

			// お問い合わせ確認メールをユーザーに送信
			$mail = $db->getData( $rec, 'mail' );
			$design	 = Template::getTemplate( $loginUserType , $loginUserRank , 'inquiry' , 'INQUIRY_MAIL' );
			Mail::send( $design , $MAILSEND_ADDRES, $mail, $gm, $rec, $MAILSEND_NAMES );

			// お問い合わせメールを管理者に送信
			$design	 = Template::getTemplate( 'admin' , $loginUserRank , 'inquiry' , 'INQUIRY_MAIL' );
			Mail::send( $design , $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES );

		}

		/**
		 * 中途/新卒求人に管理者が承認した際に、企業にメールで通知
		 *
		 * @param string $type 求人種別(mid/fresh)
		 * @param array $rec 求人レコード
		 */
		function noticeProjectActivate($type, $rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();
			$acceptTpl = Template::getTemplate($loginUserType, $loginUserRank, $type, "ACCEPT_NOTICE_MAIL");

            // 一括送信時にテンプレート側で求人種別を取得できないためセット
            $gm->setVariable('TYPE', $type);

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			self::multiSend($gm, $rec, $acceptTpl, $mailList);
		}

		/**
		 * おすすめ掲載申請時、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 */
		function noticeAttentionRequest($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM("pay_job");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$cDB = GMList::getDB("cUser");
			$cRec = $db->selectRecord($owner);

			// 企業へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'ATTENTION_REQUEST_CUSER');
			self::multiSend($gm, $rec, $design, $mailList);

			// 管理者へ通知
			if (Conf::getData("charges", "attention_notice") == "on") {
				$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'ATTENTION_REQUEST');
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
			}
		}

		/**
		 * 利用期間課金申請時、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 */
		function noticeUserLimitRequest($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM("pay_job");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$cDB = GMList::getDB("cUser");
			$cRec = $db->selectRecord($owner);

			// 企業へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'USERLIMIT_REQUEST_CUSER');
			self::multiSend($gm, $rec, $design, $mailList);

			// 管理者へ通知(課金の設定＞利用期間の更新申請通知にチェックがある場合)
			if (Conf::checkData("charges", "user_limit_target", $_GET["label"])) {
				$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'USERLIMIT_REQUEST');
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
			}
		}

		/**
		 * 中途/新卒利用期間課金の契約切れ手前のものを、企業と管理者にメールで通知
		 *
		 * @param string $type 求人種別
		 * @param string $table 求人テーブルデータ
		 * @param string $day 通知タイミング(期限○日前)
		 */
		function userLimitNotice($type, $table, $day)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$idList = self::getcUserIdList($table, $type);
			$cUserMailList = self::getcUserMailList($idList);

			$gm = GMList::getGM("pay_job");
			$db = $gm->getDB();

			switch ($type) {
				case "mid":
					$label = "MID_USERLIMIT_AD_NOTICE_MAIL";
					break;
				case "fresh":
					$label = "FRESH_USERLIMIT_AD_NOTICE_MAIL";
					break;
			}

			$gm->setVariable('limitsDay', $day);
			$design = Template::getTemplate($loginUserType, $loginUserRank, "pay_job", $label);
			$row = $db->getRow($table);

			for ($i = 0; $i < $row; $i ++) {
				// 利用期間の更新案内メールをユーザーに送信
				$rec = $db->getRecord($table, $i);

				// 退会済ユーザーの場合は送信しない
				$name = SystemUtil::getTableData('cUser', $db->getData($rec, 'owner'), 'name');
				if (empty($name)) continue;

				// 企業へ通知
				self::multiSend($gm, $rec, $design, $cUserMailList[$db->getData($rec, 'owner')]);
				// 管理者へ通知
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
			}
		}

		/**
		 * 中途/新卒求人のおすすめ掲載で契約切れ手前のものを、企業と管理者にメールで通知
		 *
		 * @param string $type 求人種別
		 * @param string $table 求人テーブルデータ
		 * @param string $day 通知タイミング(期限○日前)
		 */
		function attentionAdNotice($type, $table, $day)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$idList = self::getcUserIdList($table, $type);
			$cUserMailList = self::getcUserMailList($idList);

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			switch ($type) {
				case "mid":
					$label = "ITEMS_ATTENTION_AD_NOTICE_MAIL";
					break;
				case "fresh":
					$label = "JOB_NEW_ATTENTION_AD_NOTICE_MAIL";
					break;
			}

			$gm->setVariable('limitsDay', $day);
			$design = Template::getTemplate($loginUserType, $loginUserRank, $type, $label);
			$row = $db->getRow($table);
			for ($i = 0; $i < $row; $i ++) {
				// おすすめ求人掲載終了事前通知メールをユーザーに送信
				$rec = $db->getRecord($table, $i);
				self::multiSend($gm, $rec, $design, $cUserMailList[$db->getData($rec, 'owner')]);
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
			}
		}

		/**
		 * システム内メッセージを受信した旨を求職者/企業と管理者にメールで通知(送信通知はしない)
		 *
		 * @param array $rec システム内メッセージレコード
		 */
		function noticeReceiveMessage($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM("message");
			$db = $gm->getDB();

			$ownerType = $db->getData($rec, "owner_type");
			$destination = $db->getData($rec, "destination");
			$mailType = $db->getData($rec, "mailtype");

			switch ($ownerType) {
				case "nobody":
				case "nUser":
					$mailList = self::getcUserMail($destination);
					$noticeConf = SystemUtil::getTableData("cUser", $destination, "receive_notice");
					switch ($mailType) {
						case "inquiry":
							if (! pay_jobLogic::isAvailable($destination, "mid") || ! pay_jobLogic::isAvailable($destination, "fresh")) {
								$mailType = $mailType . "_FREE";
							}
							break;
					}
					break;
				case "cUser":
					$mailList[] = SystemUtil::getTableData("nUser", $destination, "mail");
					$noticeConf = SystemUtil::getTableData("nUser", $destination, "receive_notice");
					break;
			}
			$label = "NOTICE_RECEIVE_MESSAGE_" . $mailType;

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'message', $label);
			if ($noticeConf) {
				// 求職者または企業へメッセージ受信を通知(送信通知はしない)
				self::multiSend($gm, $rec, $design, $mailList);
			}

			// 管理者に送信
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
		}

		/**
		 * 求人情報の応募通知欄により、通知するメールアドレスを返却
		 *
		 * @param string $type 求人種別
		 * @param string $id 求人ID
		 * @return array $mailList 応募通知アドレス、または企業メールアドレス(サブメール含む)
		 */
		function getJobAddress($type, $id)
		{
			$db = GMList::getDB($type);

			$mailList = null;
			$rec = $db->selectRecord($id);
			if (isset($rec)) {
				switch ($db->getData($rec, 'notice_flg')) {
					case 'job':
						// 求人情報の応募通知アドレスに通知
						$mailList[] = $db->getData($rec, 'notice_mail');
						break;
					case 'job/cUser':
						// 応募通知アドレス、企業(メール、サブメール1・2)に通知
						$mailList[] = $db->getData($rec, 'notice_mail');
						$owner = $db->getData($rec, 'owner');
						$mailList = array_merge($mailList, self::getcUserMail($owner));
						break;
					case 'cUser':
					default:
						// 企業(メール、サブメール1・2)に通知
						$owner = $db->getData($rec, 'owner');
						$mailList = self::getcUserMail($owner);
						break;
				}
			}

			return $mailList;
		}

		/**
		 * 求職者が応募時に、非会員/求職者と企業と管理者にメールで通知
		 *
		 * エントリーにより求人が応募上限に達した場合は、企業と管理者にメールで通知
		 *
		 * @param string $userType ユーザー種別
		 * @param array $rec 応募レコード
		 */
		function EntryNotice($userType, $rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM('entry');
			$db = $gm->getDB();
			$entryUser = SystemUtil::getTableData($userType, $db->getData($rec, 'entry_user'), "mail");

			$jobType = $db->getData($rec, 'items_type');
			$job = $db->getData($rec, 'items_id');

			$mailList = self::getJobAddress($jobType, $job);

			$gm->setVariable("userType", $userType);

			// 応募通知を求人企業に送信
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'entrycUser', 'ENTRY_ALERT_MAIL');
			self::multiSend($gm, $rec, $design, $mailList);

			// 応募通知を非会員/求職者に送信
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'entrynUser', 'ENTRY_ALERT_MAIL');
			Mail::send($design, $MAILSEND_ADDRES, $entryUser, $gm, $rec, $MAILSEND_NAMES);

			// 応募通知を管理者に送信
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'entryAdmin', 'ENTRY_ALERT_MAIL');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 応募上限に達した場合その旨を求人企業と管理者に送信
			if (! JobLogic::checkApplyNumber($jobType, $job)) {
				$design = Template::getTemplate($loginUserType, $loginUserRank, 'entrycUser', 'MAX_ENTRY_MAIL');
				self::multiSend($gm, $rec, $design, $mailList);

				$design = Template::getTemplate($loginUserType, $loginUserRank, 'entryAdmin', 'MAX_ENTRY_MAIL');
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
			}
		}

		/**
		 * スカウト辞退時(求職者/自動辞退処理)、企業と管理者にメールで通知
		 *
		 * @param array $rec システム内メッセージレコード
		 */
		function noticeDeclinationScout($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM('message');
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'message', 'DECLINATION_SCOUT');

			// 管理者へ通知
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * 企業の入金を管理者が承認した際に、企業にメールで通知
		 *
		 * @param array $rec 請求レコード
		 */
		function NoticeAcceptPayment($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("bill");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'bill', 'ACCEPT_PAYMENT_MAIL');
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * 企業の入金報告時に、管理者にメールで通知
		 *
		 * @param array $rec 請求レコード
		 */
		function noticePayment($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("bill");
			$db = $gm->getDB();

			// 管理者へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'bill', 'NOTICE_PAYMENT_MAIL');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
		}

		function getcUserIdList($table, $type)
		{
			$db = GMList::getDB($type);
			$table = $db->getCountTable('owner', $table);
			$row = $db->getRow($table);

			$idList = array();
			for ($i = 0; $i < $row; $i ++) {
				$rec = $db->getRecord($table, $i);
				$idList[] = $db->getData($rec, 'owner');
			}

			return $idList;
		}

		function getcUserMailList($idList)
		{
			$db = GMList::getDB('cUser');
			$table = $db->getTable();
			$table = $db->searchTable($table, "id", "in", $idList);

			$row = $db->getRow($table);
			$mailList = array();

			for ($i = 0; $i < $row; $i ++) {
				$rec = $db->getRecord($table, $i);
				$id = $db->getData($rec, "id");
				$mailList[$id] = array(
					$db->getData($rec, "mail"),
					$db->getData($rec, "sub_mail"),
					$db->getData($rec, "sub_mail2")
				);
			}

			return $mailList;
		}

		/**
		 * ユーザーが退会時に、管理者にメールで通知
		 *
		 * @param string $type ユーザー種別
		 * @param array $rec ユーザーレコード
		 */
		function noticeResigns($type, $rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'NOTICE_RESIGNS');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
		}

		/**
		 * 管理者が中途/新卒/おすすめ求人掲載課金をキャンセル時に、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 */
		static function noticePaymentCancel($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("pay_job");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_PAYMENT_CANCEL');
			self::multiSend($gm, $rec, $design, $mailList);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_PAYMENT_CANCEL_ADMIN');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
		}

		/**
		 * おすすめ掲載承認時、承認された求人情報の勤務地を、希望勤務地としている求職者と管理者にメールで通知
		 *
		 * @param string $targetType 求人種別
		 * @param string $targetID 求人ID
		 */
		function noticeAttentionAddsUser($targetType, $targetID)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$adds = SystemUtil::getTableData($targetType, $targetID, "work_place_adds");
			if (empty($adds)) {
				return;
			}

			$rDB = GMList::getDB("resume");
			$rTable = $rDB->getTable();
			$rTable = $rDB->searchTable($rTable, "publish", "=", "on");
			$rTable = $rDB->searchTable($rTable, "hope_work_place", "=", "%{$adds}%");
			$row = $rDB->getRow($rTable);

			$gm = GMList::getGM("resume");
			for ($i = 0; $i < $row; $i ++) {
				$rRec = $rDB->getRecord($rTable, $i);
				$owner = $rDB->getData($rRec, "owner");
				$mail = SystemUtil::getTableData("nUser", $owner, "mail");

				$gm->setVariable("adds", $adds);
				$gm->setVariable("targetType", $targetType);
				$gm->setVariable("targetID", $targetID);
				$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_ATTENTION_ADDS_USER');
				Mail::send($design, $MAILSEND_ADDRES, $mail, $gm, $rRec, $MAILSEND_NAMES);
			}

			if ($row > 0) {
				$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_ATTENTION_ADDS_USER_admin');
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rRec, $MAILSEND_NAMES);
			}
		}

		/**
		 * 企業が応募進捗変更時、非会員/求職者にメールで通知
		 * 採用・不採用時には企業と管理者にも通知
		 *
		 * @param array $rec 応募レコード
		 */
		function sendEntryStatusChenge($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM("entry");
			$db = $gm->getDB();

			$userType = SystemUtil::getUserType($db->getData($rec, 'entry_user'));
			$entryUser = SystemUtil::getTableData($userType, $db->getData($rec, 'entry_user'), "mail");
			$gm->setVariable("userType", $userType);

			$status = $db->getData($rec, "status");

			switch ($status) {
				case "START":
				case "EP001":
				case "EP002":
					$label = "STATUS_CHANGE_MESSAGE_COMMON";
					break;
				case "SUCCESS":
				case "FAILE":
					$label = "STATUS_CHANGE_MESSAGE_" . $status;

					// 企業へ通知
					$owner = $db->getData($rec, "items_owner");
					$mailList = self::getcUserMail($owner);
					$design = Template::getTemplate($loginUserType, $loginUserRank, 'entry', $label . "_CUSER");
					self::multiSend($gm, $rec, $design, $mailList);

					// 管理者へ通知
					$design = Template::getTemplate($loginUserType, $loginUserRank, 'entry', $label . "_ADMIN");
					Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
					break;
				default:
					return;
			}

			// 非会員/求職者に送信
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'entry', $label);
			Mail::send($design, $MAILSEND_ADDRES, $entryUser, $gm, $rec, $MAILSEND_NAMES);
		}

		/**
		 * 企業の入金を管理者が承認キャンセルした際に、管理者にメールで通知
		 *
		 * ※企業には通知されません。必要に応じ、管理者が説明してください
		 *
		 * @param array $rec 請求レコード
		 */
		function NoticeCancelPayment($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("bill");

			// 管理者へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'bill', 'CANCEL_PAYMENT_MAIL');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);
		}

		/**
		 * 管理者がお祝い金申請に対して、振込または却下した時、非会員/求職者にメールで通知
		 *
		 * @param array $rec お祝い金レコード
		 */
		function noticeGiftPay($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			global $ACTIVE_ACCEPT;
			global $ACTIVE_DENY;
			// **************************************************************************************

			$gm = GMList::getGM("gift");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$activate = $db->getData($rec, "activate");
			$mail = SystemUtil::getTableData("nUser", $owner, "mail");

			switch ($activate) {
				case $ACTIVE_ACCEPT:
					$label = 'PAY_GIFT_MAIL';
					break;
				case $ACTIVE_DENY:
					$label = 'NOT_PAY_GIFT_MAIL';
					break;
			}

			if (empty($mail)) {
				$mail = SystemUtil::getTableData("nobody", $owner, "mail");
			}

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'gift', $label);
			Mail::send($design, $MAILSEND_ADDRES, $mail, $gm, $rec, $MAILSEND_NAMES);
		}

		/**
		 * 応募課金発生時に、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 */
		function noticeApplyPay($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("pay_job");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', "NOTICE_APPLY");

			// 管理者へ通知
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * 採用課金発生時に、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 */
		function noticeEmploymentPay($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("pay_job");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', "NOTICE_EMPLOYMENT");

			// 管理者へ通知
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * スカウト課金発生時(メッセージ閲覧時)に、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 */
		function noticeScoutMailReaded($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM('pay_job');
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_SCOUT_MAIL_READED');

			// 管理者へ通知
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * スカウト課金発生時(スカウト時)に、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 * @param string $messageID システム内メッセージID
		 */
		function noticeScoutPay($rec, $messageID)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM('pay_job');
			$gm->setVariable("message_id", $messageID);
			$gm->setVariable("message_sub", SystemUtil::getTableData('message', $messageID, 'sub'));
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			// 管理者へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_SCOUT');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * スカウト課金発生時(応募時)に、企業と管理者にメールで通知
		 *
		 * @param array $rec 契約レコード
		 * @param string $nUserID 求職者ID
		 * @param string $jobID 求人ID
		 */
		function noticeScoutApplyPay($rec, $nUserID, $jobID)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM('pay_job');
			$gm->setVariable("entry_user_id", $nUserID);
			$gm->setVariable("job_id", $jobID);
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			// 管理者へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'pay_job', 'NOTICE_SCOUT_APPLY');
			Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * 請求CRON実行時に、企業と管理者にメールで請求書を通知
		 *
		 * @param array $rec 請求レコード
		 */
		function noticeBill($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM("bill");
			$db = $gm->getDB();

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			// 管理者へ通知
			$design_admin = Template::getTemplate($loginUserType, $loginUserRank, 'bill', 'NOTICE_BILLS_ADMIN');
			Mail::send($design_admin, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

			// 企業へ通知
			$design = Template::getTemplate($loginUserType, $loginUserRank, 'bill', 'NOTICE_BILLS');
			self::multiSend($gm, $rec, $design, $mailList);
		}

		/**
		 * インタビューに管理者が承認した際に、企業にメールで通知
		 *
		 * @param array $rec インタビューレコード
		 */
		function noticeInterviewActivate($rec)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$gm = GMList::getGM('interview');
			$db = $gm->getDB();
			$acceptTpl = Template::getTemplate($loginUserType, $loginUserRank, 'interview', "ACCEPT_NOTICE_MAIL");

			$owner = $db->getData($rec, "owner");
			$mailList = self::getcUserMail($owner);

			// 企業へ通知
			self::multiSend($gm, $rec, $acceptTpl, $mailList);
		}

		/**
		 * 管理者承認申請時に、企業と管理者にメールで通知
		 *
		 * @param array $rec ユーザレコード
		 * @param string $type 求人種別
		 * @param string $mode regist/edit
		 */
		function noticeNewPending($rec, $type, $mode)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$gm = GMList::getGM($type);
			$db = $gm->getDB();

			// 企業が求人情報を登録・編集時に通知
			if ($loginUserType != "cUser") {
				return;
			}

			$check = false;
			switch ($type) {
				case 'mid':
				case 'fresh':
					$check = Conf::checkData('job', 'ad_check', $mode);
					break;
				case 'interview':
					$check = Conf::checkData('interview', 'ad_check', $mode);
					break;
				case 'default':
					break;
			}

			if ($check) {
				$owner = $db->getData($rec, "owner");
				$mailList = self::getcUserMail($owner);

				// 管理者に送信
				$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'NEW_PENDING_NOTICE_MAIL_ADMIN');
				Mail::send($design, $MAILSEND_ADDRES, $MAILSEND_ADDRES, $gm, $rec, $MAILSEND_NAMES);

				// 企業に送信
				$design = Template::getTemplate($loginUserType, $loginUserRank, $type, 'NEW_PENDING_NOTICE_MAIL');
				self::multiSend($gm, $rec, $design, $mailList);
			}
		}

		/**
		 * 1企業のメールアドレス、サブアドレス1、サブアドレス2を取得します
		 *
		 * @param string $owner 企業ID
		 * @return array $mailList メールアドレス、サブアドレス1、サブアドレス2
		 */
		private function getcUserMail($owner)
		{
			$mailList[] = SystemUtil::getTableData("cUser", $owner, "mail");

			$subMail = SystemUtil::getTableData("cUser", $owner, "sub_mail");
			if (strlen($subMail)) {
				$mailList[] = $subMail;
			}

			$subMail2 = SystemUtil::getTableData("cUser", $owner, "sub_mail2");
			if (strlen($subMail2)) {
			    $mailList[] = $subMail2;
			}

			return $mailList;
		}

	}

