<?php

$UPDATE_NAME = 'add_sub_update';

$UPDATE_NAMES[ ] = $UPDATE_NAME;
$UPDATE_DESCRIPTION[ $UPDATE_NAME ] = 'add_subの更新に伴ない吸収合併されたIDを移動させる。';

$UPDATE_CLASS[ $UPDATE_NAME ] = "update_add_sub";
$UPDATE_METHOD[ $UPDATE_NAME ] = "merger";

class update_add_sub
{
	function merger(){
		//対象となるテーブルとレコード、このアップデータが必要なパッケージ毎に設定する。
		$add_sub_relation= Array(
			'nUser' => 'add_sub',
			'cUser' => 'add_sub' ,
			'mid' => 'work_place_add_sub',
			'fresh' => 'work_place_add_sub',
			'nobody' => 'add_sub'
		);
		$add_sub_table_name = "add_sub";
		
		$aDB = GMList::getDB($add_sub_table_name);
		$aTable = $aDB->getTable('d');
		
		$no_merger = array();
		
		//対象となるテーブルの数だけ走査
		foreach( $add_sub_relation as $table_name => $clm )
		{
			//結合して 削除済みのadd_subのレコードを参照している 対象テーブルのレコード一覧を取得する。
			$uDB = GMList::getDB( $table_name );
			
			$uTable = $aDB->joinTable( $aTable, $add_sub_table_name."_delete", $table_name, "id", $clm );
			$uTable = $aDB->getColumn( 'merger' ,$uTable );
			$uTable = $aDB->addSelectColumn( $uTable, 'id',true,$table_name );
			
			$row = $aDB->getRow($uTable);
			if( $row <= 0 ){ continue; }
			
			$list = $aDB->getDataList( $uTable, 'merger','id');
			
			foreach( $list as $id => $merger )
			{
				//mergerが設定されているかを確認する。
				if( is_null($merger) || !strlen($merger) ) 
				{
					$no_merger[] = "テーブル $table_name の $id の  $clm はマージ対象が存在しない為、更新できません。　市政化による区への移動等の可能性が高いです。<br/>\n";
					
					
					continue;
				}
				//設定されている場合は 新しい行をいれる。
				
				$rec = $uDB->selectRecord( $id );
				
				$uDB->setData( $rec, $clm, $merger );
				$uDB->updateRecord( $rec );
				
				print "テーブル $table_name の $id の $clm カラムを $merger に更新しました。<br/>\n";
			}
			
			//削除されているが、mergerに一致するものが無い場合は 区分追加などで自動ではどうにもならないが。
			//とりあえずそれらのIDと名前？の一覧を表示するようにする。
			if( count($no_merger) ){
				print join($no_merger);
			}
		}
	}
}