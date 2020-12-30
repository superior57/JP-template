<?php

require_once './include/base/Initialize.php';


class system_tablesInit implements Initialize{
	public function initTable( $target_table = null ){
		global $TABLE_NAME;
		global $TABLE_PREFIX;
		
		$t_prefix = strtolower( $TABLE_PREFIX );
            
		$ccDB = GMList::getDB('system');
	
		$max_shadow_id = 0;
		$result                = $ccDB->sql_query( 'SELECT max(abs(shadow_id)) as max_id FROM ' . $t_prefix.'system_tables'  );
		if($result){
			if($ccDB->sql_num_rows($result) != 0){
				$rec = $ccDB->sql_fetch_assoc( $result, 0);
				$max_shadow_id = $rec['max_id']-0;
			}
		}
		
		$table_list = $TABLE_NAME;
		if( !is_null($target_table )){ $table_list = array( $target_table ); }
		
		foreach( $table_list as $name ){
			$max_id = 0;
			$real_table_name = $t_prefix . strtolower( $name );
			
			//table check
			$result                = $ccDB->sql_query( 'SELECT max(abs(shadow_id)) as max_id FROM ' .$real_table_name );
			if($result){
				if($ccDB->sql_num_rows($result) != 0){
					$rec = $ccDB->sql_fetch_assoc( $result, 0);
					$max_id = $rec['max_id']-0;
				}
			}
		
			//delete table check
			$result                = $ccDB->sql_query( 'SELECT max(abs(shadow_id)) as max_id FROM ' . $real_table_name.'_delete'  );
			if($result){
				if($ccDB->sql_num_rows($result) != 0){
					$rec = $ccDB->sql_fetch_assoc( $result, 0);
					
					if( $max_id < $rec['max_id']-0 ){
						$max_id = $rec['max_id']-0;
					}
				}
			}
			
			//shadow_id get
			$result = $ccDB->sql_query( 'SELECT shadow_id FROM '. $t_prefix .'system_tables WHERE table_name = "'.$name. '"' );
			$update = false;
			if($result){
				if($ccDB->sql_num_rows($result) != 0){
					$rec = $ccDB->sql_fetch_assoc( $result, 0);
					
					//update
					$result = $ccDB->sql_query( 'UPDATE '. $t_prefix .'system_tables SET id_count = '.$max_id." WHERE shadow_id = ". $rec['shadow_id'] );
				
					if(!$result){
						//d('UPDATE '. $t_prefix .'system_tables SET id_count = '.$max_id." WHERE shadow_id = ". $rec['shadow_id'],"error");
						break;
					}
					//print "update {$name} count {$max_id} <br/>";
					$update= true;
				}
			}
			if( !$update ){
				//inesrt
				$max_shadow_id++;
				$result = $ccDB->sql_query( 'INSERT INTO '. $TABLE_PREFIX .'system_tables (shadow_id,table_name,id_count) VALUES ('.$max_shadow_id.',"' . $name .'",'.$max_id . ')' );
				print "insert {$name} count {$max_id} <br/>";
			}
			
			
			if(!$result){
				//d('INSERT INTO '. $TABLE_PREFIX .'system_tables (shadow_id,table_name,id_count) VALUES ('.$max_shadow_id.',"' . $name .'",'.$max_id . ')','error' );
				break;
			}
		}
		if( is_null($target_table) ){
			print '<p>システムテーブルの初期化に成功しました。</p>';
		}else{
			print '<p>システムテーブルの'.$target_table.'の値を更新しました。</p>';
		}
	}
}