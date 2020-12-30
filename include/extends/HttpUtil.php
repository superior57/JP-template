<?php
class HttpUtil{
	// -------------------------------------------------------------------------
	// array http_request(string Method, string URI, string Version [, array headers
	//                    [, string Server [, int Size [, int Timeout1 [, int Timeour2]]]]])
	// URIに対してMethodでHTTPリクエストを行います。HTTPのバージョンはVersionで指
	// 定できます。追加のリクエスヘッダーは連想配列headersで指定します。
	// 返り値にはHTTP-Version、Status-Code、Reason-Phraseが必ず含まれ、それ以外
	// にサーバが返した情報（index: value）が含まれます。
	// Status-Codeが9xxの場合、それはホストが存在しない場合などHTTPリクエストが
	// 正常に行われなかったことを意味します。
	// -------------------------------------------------------------------------
	static function http_request($method, $uri, $version, $headers = array(), $server = "", $limit_size = 51200, $tos = 2, $tor = 4)
	{

		if ($server == "") {
			// serverが空の場合はuriから取得
			// absoluteURIならそこから
			if ($uri && substr($uri, 0, 1) != "/") {
				$temp = parse_url($uri);
				$host = $temp["host"];
				$port = $temp["port"];
				// それ以外ならHostフィールドから
			} else {
				// サーバをホスト名とポートに分離
				$temp = explode(":", $headers["Host"]);
				$host = $temp[0];
				$port = $temp[1];
			}
		} else {
			// サーバをホスト名とポートに分離
			$temp = explode(":", $server);
			$host = $temp[0];
			$port = $temp[1];
		}

		// ポートが空の時はデフォルトの80にします。
		if (! $port) {
			$port = 80;
		}


		// リクエストフィールドを制作。
		$msg_req = $method . " " . $uri . " HTTP/". $version . "\r\n";
		foreach ($headers as $name => $value) {
			$msg_req .= $name . ": " . $value . "\r\n";
		}
		$msg_req .= "\r\n";

		$status = array();
		// 指定ホストに接続。
		if ($handle = @fsockopen($host, $port, $errno, $errstr, $tos)) {
			if (socket_set_timeout($handle, $tor)) {
				fputs ($handle, $msg_req);
				$buffer = fread($handle, $limit_size);
				fclose ($handle);

				$status = array();
				$status["Raw-Data"] = $buffer;
				$temp = explode("\r\n\r\n", $buffer);
				$buffer_header = array_shift($temp);
				$entity_body = implode("\r\n\r\n", $temp);

				$temp_line = explode("\r\n", $buffer_header);
				foreach ($temp_line as $line_no => $line_contents) {
					if($line_no == 0) {
						$temp_status = explode(" ", $line_contents);
						$status["HTTP-Version"] = $temp_status[0];
						$status["Status-Code"] = $temp_status[1];
						$status["Reason-Phrase"] = $temp_status[2];
					} else {
						$temp_status = explode(":", $line_contents);
						$field_name = array_shift($temp_status);
						$status[$field_name] = ltrim(implode(":", $temp_status));
					}
				}
				if ($entity_body != "") {
					$status["entity-body"] = $entity_body;
				}
			} else {
				$status["HTTP-Version"] = "---";
				$status["Status-Code"] = "902";
				$status["Reason-Phrase"] = "Response Timeout";

			}
		} else {
			$status["HTTP-Version"] = "---";
			$status["Status-Code"] = "901";
			$status["Reason-Phrase"] = "Connection Timeout";
		}

		return $status;
	}

	/*
	 PHP5専用関数　POST／https対応ページデータの取得

	 *	@param $url	取得ページＵＲＬ
	 *	@param $param 連想配列での引数（POST）※省略可

	 取得可能な形式例
	 print getURL("http://www.hoge.com/hoge.php");
	 print getURL("http://www.hoge.com/hoge.php?param1=value1");
	 print getURL("http://www.hoge.com/hoge.php", array('param2'  => 'value2', 'param3'  => 'value3'));
	 print getURL("http://www.hoge.com/hoge.php?param1=value1", array('param2'  => 'value2', 'param3'  => 'value3'));
	 print getURL("https://www.hoge.com/hoge.php", array('param2'  => 'value2', 'param3'  => 'value3'));
	 print getURL("https://www.hoge.com/hoge.php?param1=value1", array('param2'  => 'value2', 'param3'  => 'value3'));
	 */
	static function getURL($url, $param = null){

		$ch=curl_init();
		curl_setopt ($ch,CURLOPT_URL,$url);

		if(!is_null($param)){
			curl_setopt ($ch,CURLOPT_POST,1);

			$post = http_build_query($param,'','&');
			curl_setopt ($ch,CURLOPT_POSTFIELDS,$post);
			curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
			curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1);
		}else{
			curl_setopt ($ch, CURLOPT_GET, 1);
		}

		$return = curl_exec($ch);
		curl_close ($ch);
		
		return $return;
	}
}
?>