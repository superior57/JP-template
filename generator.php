<?php
include "./custom/head_main.php";

try {
	new variableSelecterGenerator();
}catch (Exception $e){
	print $e->getMessage();
}

/**
 * 各DBのカラムから特殊コードを挿入するためのセレクトボックスの雛形を生成するクラス。
 */
class variableSelecterGenerator{

	private $spHead = "[###--";
	private $spFoot = "--###]";
	private $acceptType = array("cUser","nUser");
	private $setFilePath ;

	function __construct(){
		global $loginUserType;
		global $loginUserRank;

		$this->setFilePath = Template::getTemplate($loginUserType, $loginUserRank, "cmsSPCode", "VARIABLE_SELECTOR_PARTS_TEMP");
		if( !$this->setFilePath || $this->setFilePath == null || $this->setFilePath == ""){ return ;}
		$this->generate();
	}

	/*
	 *  下書きファイルに変数挿入用のプルダウンのccを出力する
	 */
	private function generate(){
		file_put_contents($this->setFilePath, $this->getTemplateString());
	}

	private function addSPCode($val){
		return $this->spHead.$val.$this->spFoot;
	}

	/*
	 *  lstのサマリーを取得する
	 */
	function getSelectVariable($gm,$type){
		$data = array();
		foreach($gm->colSummary as $column => $summry){
			if(strpos($summry, "//") !== 0){
				$data["column"][] = $column;
				$data["sammary"][] = str_replace(" ","\ ", $summry);
			}
		}
		return $data;
	}

	/*
	 * form optionのccを取得する
	 */
	private function getTemplateString(){
		global $loginUserType;
		global $loginUserRank;
		global $TABLE_NAME;

		$cGM = SystemUtil::getGMforType("system");
		$template = Template::getTemplate($loginUserType, $loginUserRank, "cmsSPCode", "VARIABLE_SELECTOR_TEMPLATE");

		$buffer = "";
		foreach ($TABLE_NAME as $type){
			if(!in_array($type, $this->acceptType))
				continue;

			$gm = GMList::getGM($type);
			$data = $this->getSelectVariable($gm,$type);

			$colnames = array();
			$spStrArray = array_map(array($this,"addSPCode"), $data["column"]);
			$spStr = implode("/", $spStrArray);
			$sammaryStr = implode("/", $data["sammary"]);

			$cGM->setVariable("type", $gm->db->tablePlaneName);
			$cGM->setVariable("column", $spStr);
			$cGM->setVariable("column_description",$sammaryStr);

			$buffer .= ltrim($cGM->getString($template,null,"separate"));
			$buffer .= ltrim($cGM->getString($template,null,"variableSelector"));
		}
		$buffer = str_replace(array("!COMMANDHEAD","!COMMANDFOOT"), array("<!--#","#-->"), $buffer);
		return $buffer;
	}
}
