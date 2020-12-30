<?php

	class checkDataAppend //
	{
		function __construct( $iCheckData ) //
		{
			$this->checkData = $iCheckData;
			$this->check     = $iCheckData->check;
			$this->gm        = $iCheckData->gm;
			$this->type      = $iCheckData->type;
			$this->data      = $iCheckData->data;
			$this->edit      = $iCheckData->edit;
		}

		function addError( $iPart , $iDef = null , $iName = null ) //
			{ return $this->checkData->addError( $iPart , $iDef , $iName ); }

		function addErrorString( $iStr ) //
			{ return $this->checkData->addErrorString( $iStr ); }
	}
