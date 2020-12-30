<?php

class mod_viewMode extends command_base
{
	//デフォルトで表示する案件タイプを取得する
	function getSelectedType( &$gm, $rec, $args ){
		$this->addBuffer(viewMode::getViewMode());
	}
}