<?php
class commonDL{

	protected $gm 	= null;
	protected $db 	= null;
	protected $sys 	= null;
	protected $param	= null;

	function __construct($param){
		global $gm;
		ConceptSystem::CheckType()->OrThrow();

		$this->type = $param[ 'type' ];
		$this->sys  = SystemUtil::getSystem( $this->type );
		$this->gm   = $gm;
		$this->param = $param;
	}

	protected function setCSVLabel(&$out,$header){
		global $SYSTEM_CHARACODE;
		mb_convert_variables("sjis",$SYSTEM_CHARACODE,$header);
		fputcsv($out,$header);
	}

	protected function drawDownloadError(){
		global $loginUserType;
		global $loginUserRank;
		print System::getHead( $this->gm , $loginUserType , $loginUserRank );
		Template::drawTemplate($this->gm[$this->type], null, $loginUserType, $loginUserRank, "", "UNDEFINED_DOWNLOAD_DATA");
		print System::getFoot( $this->gm , $loginUserType , $loginUserRank );
		exit;
	}

	protected function search(){
		global $magic_quotes_gpc;

		$search = new Search( $this->gm[$this->type] , $this->type);
		$searchParam  = $this->param;

		if( !$magic_quotes_gpc && 'sjis' == $this->gm[$this->type]->getDB()->char_code ) //文字化け対策が必要な環境の場合
		{ $searchParam = addslashes_deep( $searchParam );
		}

		$search->setParamertorSet( $searchParam );
		$table = $search->getResult();
		return $table;
	}
}