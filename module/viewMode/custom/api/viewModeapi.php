<?php

class mod_viewModeApi extends apiClass{

	function tempChangeViewMode( &$param )
	{
		viewMode::setViewMode($param["view_mode"]);
		echo "Ok";
	}
}