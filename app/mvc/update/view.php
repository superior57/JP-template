<?php

	//★クラス //

	/**
		@brief 既定のアップデート通知のビュー。
	*/
	class AppUpdateView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     アップデート情報取得用のjsコードを出力する。
			@param[in] $iModel モデルインスタンス。
		*/
		function drawUpdateScriptCode( $iModel ) //
		{
			$text1 = 'このパッケージに該当する最新バージョンのアップデートファイルを確認しています...';
			$text2 = '利用可能なアップデートが <strong>"+json["update_num"]+"</strong> 件あります。';
			$text3 = 'ログインが必要なページへ移動します、よろしいですか？';
			$text4 = 'パッケージバージョン ： <strong>"+json["version"]+"</strong> ／ 最終チェック日時 ： <strong>'.date('Y.m.d H:i',filemtime($iModel->updateLogFile)).'</strong>';
			$url   = 'keygen.php?mode=save';

			?>
				var SELF = (function (e) { if(e.nodeName.toLowerCase() == 'script') return e; return arguments.callee(e.lastChild) })(document);
				var cookie = "<?php print $_COOKIE["changeLogCache"]; ?>";

				var element = document.createElement('div');
				element.id = "publicApi";
				element.innerHTML = "<p style='padding:10px; border:2px solid #ccc;'><?php echo mb_convert_encoding($text1,mb_internal_encoding(),'utf-8'); ?></p>";
				SELF.parentNode.appendChild(element);

				if(cookie == "true"){
					var script=document.createElement('script');
					script.src="<?php print $iModel->updateLogFile; ?>";
					script.charset="UTF-8";
					document.body.appendChild(script);
				}else{
					var script=document.createElement('script');
					script.src="<?php print  $iModel->updateCheckURL ?>";
					script.charset="UTF-8";
					document.body.appendChild(script)

					var div=document.createElement("div");
					div.id="package_version";
					document.body.appendChild(div);

					$("#package_version").css({"position":"fixed","left":"0px","top":"0px","z-index":"1000","background-color":"#444","color":"#efefef","padding":"5px","text-align":"center","width":"100%","cursor":"pointer","display":"none"});
					$("#package_version").html("<?php echo mb_convert_encoding($text1,mb_internal_encoding(),'utf-8'); ?>");
					$("#package_version").slideDown();
				}
				window.callback = function(json){
					var text = "";

					document.getElementById('publicApi').innerHTML=json["html"];

					if(json["version"] != false){
						if(json["update_num"]>0){
							setTimeout( function() {
								$("#package_version").slideUp("");
								document.getElementById('version').innerHTML= "<?php echo mb_convert_encoding($text2,mb_internal_encoding(),'utf-8'); ?>";
								$("#package_version").slideDown().html(document.getElementById('version').innerHTML);
								$("#package_version").on("hover",function(){ $(this).css("background-color","#666"); },function(){ $(this).css("background-color","#444"); });
								$("#package_version").on("click",function(){ window.open(json["url"]); });
							},1500);
						}else{
							setTimeout( function() {
								$("#package_version").slideUp("");
								document.getElementById('version').innerHTML="<?php echo mb_convert_encoding($text4,mb_internal_encoding(),'utf-8'); ?>";
							},1500);
						}
					}
					$("#publicApi #pds_update_information li a").each(function(){
						$(this).attr("target","_blank");
						$(this).on("click",function(){
							if($(this).parent().parent().attr("class")=="order"){
								if(window.confirm('<?php echo mb_convert_encoding($text3,mb_internal_encoding(),'utf-8'); ?>')){
									return true;
								}else{
									return false;
								}
							}else{
								return true;
							}
						});
					});
					if(!cookie){
						var script=document.createElement('script');
						script.src="./<?php echo $url ?>";
						script.charset = "UTF-8";
						document.body.appendChild(script);
					}
				}
			<?php
		}
	}
