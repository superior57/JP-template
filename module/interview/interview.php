<?php

class mod_interview extends command_base //
{
	function drawCuserInfo( &$gm, $rec, $args )
	{
		global $loginUserType;
		global $NOT_LOGIN_USER_TYPE;
		global $loginUserRank;

		$design = Template::getTemplate( $loginUserType , $loginUserRank , "cUserCheckForm" , 'INCLUDE_DESIGN' );
		
		$gm		 = SystemUtil::getGMforType( 'cUser' );
		$db		 = $gm->getDB();
		$rec=$db->selectRecord($args[0]);
		
	
		$this->addBuffer( $gm->getString( $design , $rec,$label ) );
	}
}
