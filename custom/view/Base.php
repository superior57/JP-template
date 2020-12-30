<?php

	class CategoryView extends command_base
	{
		function Sample( &$gm, $rec, $args )
		{
			$this->addBuffer( "------------ unmounting ------------<br/>".__FILE__ ."<br/>".__METHOD__ );
		}
	}