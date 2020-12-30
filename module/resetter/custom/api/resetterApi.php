<?php

	class mod_resetterApi extends apiClass
	{
		private $type = null;
		private $id = null;

		function sendValidity(&$param){
			global $THIS_TABLE_IS_USERDATA;
			global $TABLE_NAME;
			global $gm;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;

			$sGM = GMList::getGM("system");
			$design = Template::getTemplate("nobody", 15, "resetter", "RESETTER_VALIDITY_MAIL");

			$type = $param["type"];
			$mail = $param["mail"];
			$result = "ng";

			if(!CheckDataBase::is_mail($mail))	$result = "mailFormat";

			$max	 = count($TABLE_NAME);
			for($i=0; $i<$max; $i++){
				if(  $THIS_TABLE_IS_USERDATA[ $TABLE_NAME[$i] ]  ){
					$db		 = $gm[ $TABLE_NAME[$i] ]->getDB();
					$table	 = $db->getTable();
					$table	 = $db->searchTable($table, 'mail', '=', $mail);
					if( $db->existsRow($table) )
					{
						$rec = $db->getFirstRecord($table);
						$type = $TABLE_NAME[$i];
						$id = $db->getData($rec,"id");
						$result = "dup";
						break;
					}
				}
			}
			
			$token=md5("passwordResetter_".time().$mail);
			
			$url = "index.php?app_controller=other&key=resetter_conf&token=".$token;
			$sGM->setVariable("url", $url);

			if($result == "dup"){
				$fp    = fopen( 'file/reminder/' . $token , 'wb' );
				fputs( $fp , implode( ',' , Array( $type,$id ) ) );
				fclose( $fp );
			
				Mail::send($design, $MAILSEND_ADDRES, $mail, $sGM);
			}

			print $result;
		}
		
		
		function newPasswordRegist(&$param){
			global $gm;

			$result = "ng";
			$pass = $param["pass"];
			$this->cleanUpResetToken();
			if($this->verifyResetToken($param["token"])){
				$db	 = $gm[ $this->type ]->getDB();
				$rec = $db->selectRecord($this->id);

						$db->setData($rec,"pass",$pass);
						$db->updateRecord($rec);

				unlink( 'file/reminder/' . preg_replace( '/\W/' , '' , $param["token"] ) );
						$result = "dup";
					}

			print $result;
				}

		function cleanUpResetToken() //
		{
			$dir = opendir( 'file/reminder/' );

			while( $dir && $entry = readdir( $dir ) )
			{
				if( '.' == $entry || '..' == $entry )
				{ continue; }

				$stat = stat( 'file/reminder/' . $entry );

				if( time() - $stat[ 'mtime' ] > 60 * 60 * 30 )
				{ unlink( 'file/reminder/' . $entry ); }
			}
			
			closedir( $dir );
		}

		function verifyResetToken($token) //
		{

			if( file_exists( 'file/reminder/' . preg_replace( '/\W/' , '' , $token ) ) )
			{
				$fp   = fopen( 'file/reminder/' . preg_replace( '/\W/' , '' , $token ) , 'rb' );
				$data = fgets( $fp );

				fclose( $fp );

				List( $this->type , $this->id ) = explode( ',' , $data );

				$db  = GMList::getDB( $this->type );
				$rec = $db->selectrecord( $this->id );

				if( $rec )
					{ return true; }
			}
			return false;
		}

	}

?>