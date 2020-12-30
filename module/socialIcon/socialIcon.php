<?php

	class mod_socialIcon extends command_base
	{
		function drawSocial( $iGM_ , $iRec_ , $iArgs_)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $HOME;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$file = Template::getTemplate( $loginUserType , $loginUserRank , '', 'SOCIAL_ICON_DESIGN' );

			$type = array_shift($iArgs_);
			$id = array_shift($iArgs_);
			$title = implode( " ", $iArgs_ );
			if( strlen($title) == 0 ) { $title = SystemUtil::getSystemData('site_title'); }
			$mixi_check = SystemUtil::getSystemData('mixi_check');

			$url = $HOME;
			if( strlen($type) > 0 && strlen($id) > 0  )
			{ $url .= 'index.php?app_controller=info&type='.$type.'&id='.$id; }
			$url_encode = urlencode($url);

			$socialList = SystemUtil::getSystemData('social_icon');
			if( strlen($socialList) == 0 ) { return; }
			$socialList = explode( '/', $socialList );
			
			$iGM_->setVariable( 'URL', $url );
			$iGM_->setVariable( 'URL_ENCODE', $url_encode );
			$iGM_->setVariable( 'TITLE', $title );
			$iGM_->setVariable( 'MIXI_CHECK', $mixi_check );

			$buffer .= $iGM_->getString($file, null, "head");
			foreach( $socialList as $social )
			{
				if( $social == 'mixi' && strlen($mixi_check) == 0 ) { continue; }
				$buffer .= $iGM_->getString($file, null, $social);
			}
			$buffer .= $iGM_->getString($file, null, "foot");

			$this->addBuffer($buffer);
		}
		
	}
	
?>