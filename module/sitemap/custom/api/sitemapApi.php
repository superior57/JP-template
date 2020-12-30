<?php

class mod_sitemapApi
{
    public function updatesitemap()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        // **************************************************************************************

		$result = array('status'=>'error');
        if ($loginUserType != "admin") {
			print json_encode($result);
			return;
        }

		if(DIRECTORY_SEPARATOR == '\\'){
			// 非posix系は素通し
			$result['status'] = 'OK';
		}else{
			$cwd = getcwd();
			if($cwd !== FALSE){
				clearstatcache(TRUE, $cwd);
				$eid = posix_geteuid();
				$owner = fileowner($cwd);
				$group = filegroup($cwd);
				
				if($eid == $owner || $eid == $group){
					// suEXEC っぽいので作成に問題なし。
					$result['status'] = 'OK';
				}else{
					$perms = fileperms($cwd);
					if(($perms & 00002) == 0){
						// otherに書き込み権なし
						$result['mes'] = 'Webサーバーによるインストールディレクトリへの書き込みができない状態である為、ファイルを作成できません。'."\n";
						$result['mes'] .='次のディレクトリにotherの書き込み権を設定してください。'."\n";
						$result['mes'] .=$cwd;
					}else{
						// otherに書き込み権あり
						$result['status'] = 'OK';
					}
				}
			}
		}
		if($result['status']=='OK'){
	        SitemapLogic::create();
		}
		print json_encode($result);
		return;
    }

    public function deletesitemap() {
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		$result = array('status' => 'error');
		if ($loginUserType != "admin") {
			print json_encode($result);
			return;
		}

		if (SystemUtil::isWindows()) {
			// 非posix系は素通し
			$result['status'] = 'OK';
		} else {
			$cwd = getcwd();
			if ($cwd !== FALSE) {
				clearstatcache(TRUE, $cwd);
				$eid = posix_geteuid();
				$owner = fileowner($cwd);
				$group = filegroup($cwd);

				if ($eid == $owner || $eid == $group) {
					// suEXEC っぽいので作成に問題なし。
					$result['status'] = 'OK';
				} else {
					$perms = fileperms($cwd);
					if (($perms & 00002) == 0) {
						// otherに書き込み権なし
						$result['mes'] = 'Webサーバーによるインストールディレクトリへの書き込みができない状態である為、ファイルを削除できません。' . "\n";
						$result['mes'] .= '次のディレクトリにotherの書き込み権を設定してください。' . "\n";
						$result['mes'] .= $cwd;
					} else {
						// otherに書き込み権あり
						$result['status'] = 'OK';
					}
				}
			}
		}
		if ($result['status'] == 'OK') {
			SitemapLogic::delete();
		}
		print json_encode($result);
		return;
	}

}
