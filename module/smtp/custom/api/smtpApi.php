<?php
class mod_smtpApi extends apiClass {
    /**
     * テストメールの送信
     */
    public function sendTestMail($params) {
        global $SYSTEM_CHARACODE;
        global $loginUserType;
        global $loginUserRank;
        if($loginUserType != 'admin') {
            return;
        }

        header('Content-Type: application/json;charset=' . $SYSTEM_CHARACODE);
        $json = array();

        $gm = GMList::getGM(self::$type);

		$design	 = Template::getTemplate( $loginUserType , $loginUserRank , self::$type, 'SMTP_TEST_MAIL' );
        try {
            $result = SMTPLogic::sendTest($design, $gm, $params['host'], $params['username'], $params['password'], $params['secure'], $params['port'], $params['test_mail']);
			if($result['status']=='OK'){
				$json["result"] = "success";
				$json["message"] = '送信成功';
				$json['log'] = $result['message'];
			}else{
				$json["result"] = 'error';
				$json["message"] = '送信失敗';
				$json['log'] = $result['message'];
			}
        } catch(Exception $e) {
            $json["result"] = "error";
            $json["message"] = '送信失敗';
        }
        print json_encode($json);
        return;
    }
    private static $type = 'smtp_conf';
}