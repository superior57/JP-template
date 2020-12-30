<?php

	class mod_list extends command_base
	{
		/*
		 *  メール配信対象のユーザーIDを描画する
		 */
		function drawMailReceiveList( &$_gm , $_rec , $_args ){
			List($user_type,$id) = $_args;
			$this->addBuffer(implode(self::getMailReceiveList($user_type,$id),"/"));
		}

		/*
		 *  配信対象のユーザーIDを取得する
		 *  $user_type	ユーザー種別
		 *  $id			リストID
		 *
		 *  return array ユーザーID
		 */
		function getMailReceiveList($user_type,$id){
			$uTable = DMList::getUserTable($id);
			$uDB = GMList::getDB($user_type);
			return $uDB->getDataList($uTable, "id",null);
		}

		function tableSelectForm( &$gm , $_rec , $args ){
				$nrec = $_rec;
				if(isset($args[4]) && strlen($args[4]))
					$check = $args[4];
				else
					$check = "";

				if(isset($args[6]) && strlen($args[6]))
					$option = ' '.$args[6];
				else
					$option = "";

				$tgm = SystemUtil::getGMforType( $args[1] );
				$db = $tgm->getDB();

				$table = $db->getTable();

				if(isset($args[7])){
					for($i=0;isset($args[$i+7]);$i+=3){
						$table = $db->searchTable( $table, $args[7+$i], $args[8+$i], $args[9+$i] );
					}
				}

				$row = $db->getRow( $table );

				$index = Array();
				$value  = Array();

				if( isset($args[5]) && strlen($args[5]) ){
					$index[] = SystemUtil::systemArrayEscape( $args[5] );
					$value[] = "";
				}

				for($i=0;$i<$row;$i++){
					$_rec = $db->getRecord( $table , $i );
					$index[] = SystemUtil::systemArrayEscape($db->getData( $_rec , $args[2] ) );
					$value[] = SystemUtil::systemArrayEscape($db->getData( $_rec , $args[3] ) );
				}

				$index = join('/',$index);
				$value = join('/',$value);

				$param = Weave::Get( 'tagParam' , 'tableSelectForm' );

				if( count( $param ) )
				{
					if( !$option )
						{ $option = ' ' . implode( '\ ' , $param ); }
					else
						{ $option .= '\ ' . implode( '\ ' , $param ); }
				}


				$this->addBuffer( $gm->getCCResult( $nrec, '<!--# form option '.$args[0].' '.$check.' '.$value.' '.$index.$option.' #-->' ) );

		}

		function getType(){
			return "list";
		}
	}
