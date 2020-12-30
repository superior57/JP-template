<?php
class mod_cmsApi extends apiClass{

	function loadHtml($param){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $SYSTEM_CHARACODE;
		// **************************************************************************************
		if($loginUserType == "admin"){
			List($id , $user_type) = explode("_",$param["id"]);
			$path = $param["path"];
			$mode = $param["mode"];

			if(!mod_cms::existsPath($id, $user_type,$path))
				{ return; }

			print mod_cms::getHtmlFile($path,"edit",$mode);
		}
	}

	function saveHtml($param){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $SYSTEM_CHARACODE;
		global $ROOTING_INFO;
		// **************************************************************************************
		if($loginUserType == "admin"){
			List($id , $user_type) = explode("_",$param["id"]);
			$type = $ROOTING_INFO[$id]["type"];
			$path = $param["path"];
			$mode = $param["mode"];
			if(!mod_cms::existsPath($id, $user_type,$path))
				{ return; }

			$path = mod_cms::getTemplatePath($path, "edit",$mode);
			$html = $param["contents"];

			$html = rawurldecode($html);

			$html = mod_cms::convertSpecialChars($html,$type);
			file_put_contents($path, $html);
		}

		header('Content-Type: text/html;charset='.$SYSTEM_CHARACODE );
	}

	function resetHtml($param){
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $SYSTEM_CHARACODE;
		global $template_path;

		// **************************************************************************************
		if($loginUserType == "admin"){
			List($id , $user_type) = explode("_",$param["id"]);
			$path = $param["path"];
			$mode = $param["mode"];
			if(!mod_cms::existsPath($id, $user_type,$path))
				{ return; }

			$html = mod_cms::getHtmlFile($path,"backup",$mode);
			$currnet = $this->getTemplatePath($mode);

			file_put_contents($currnet.$path, $html);
		}

		header('Content-Type: text/html;charset='.$SYSTEM_CHARACODE );
		echo "ok";
	}

	function resetPage($param){
		global $loginUserType;
		global $template_path;
		global $ROOTING_INFO;

		if($loginUserType == "admin"){
			List($id , $user_type) = explode("_",$param["id"]);

			$paths = $ROOTING_INFO[$id]["path"][$user_type];
			$mode = $param["mode"];
			$currnet = $this->getTemplatePath($mode);

			foreach($paths as $path){
				if(!mod_cms::existsPath($id, $user_type,$path))
					{ return; }

				$html = mod_cms::getHtmlFile($path,"backup",$mode);
				file_put_contents($currnet.$path, $html);
			}
		}
		header('Content-Type: text/html;charset='.$SYSTEM_CHARACODE );
	}

	function getTemplatePath($mode = "mobile"){
		global $template_path;
		return $currnet = $mode=="mobile"?"./template/mobile/":$template_path;
	}
}