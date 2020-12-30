<?php

class mod_violation_report extends command_base //
{
	function drawViolationReportButton( &$iGM_ , $iRec_ , $iArgs_ ) //
	{
		global $loginUserType;
		global $loginUserRank;

		$target_type  = array_shift( $iArgs_ );
		$target_id  = array_shift( $iArgs_ );
		
		$iGM_->setVariable("target_id",$target_id);
		$iGM_->setVariable("target_type",$target_type);
		
		$design = Template::getTemplate($loginUserType, $loginUserRank, "violation_report", "VIOLATION_REPORT_BUTTON");

		$this->addBuffer( $iGM_->getString( $design , $iRec_,$label ) );
	}
}
