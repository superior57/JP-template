<?php

class ImportLogic
{
	var $type = '';
	var $check_name = '';
	var $gm;
	var $dataList;
	var $idList;
	var $owner;
	var $isOwner = false;
	var $id_update = false;
	var $delete = false;

	function __construct( $data_csv, $col_csv, $owner = '' )
	{
		if(strlen($this->type)===0){
			return;
		}
		if(!$_SESSION[ 'loginedAdminTool' ] ){ //ログインしていない
			return;
		}
		$this->gm = GMList::getGM($this->type);

		$cl = new CsvLogic( $data_csv, $col_csv, $this->check_name );
		$this->dataList = $cl->getDataList();
		$this->idList = $cl->getIdList();
		if( strlen($owner) > 0 )
		{
			$this->owner = $owner;
			$this->isOwner = true;
		}
	}

	function action()
	{
		$dataList = $this->dataList;
		$idList = $this->idList;

		$allCount = count($idList);
		if( $allCount == 0 ) { return; }// チェックIDが存在しない場合処理を実行しない

		// 標準で初期値が設定されているものの場合は削除する
		if( $this->delete ) { $this->deleteRecod( $idList ); }

		// 既にIDが存在するものはデータを更新
		$editIdList= $this->editRecordList( $dataList, $idList );
		$editCount = count($editIdList);

		// 編集したデータは削除し、その他のデータを登録
		$dataList = $this->deleteDataList( $dataList, $editIdList );
		$addIdList = $this->addRecordList( $dataList );
		$addCount = count($addIdList);

		return array( 'all'=>$allCount, 'add'=>$addCount, 'edit'=> $editCount );
	}

	function deleteRecod( $idList )
	{
		$db = GMList::getDB($this->type);

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'id', 'not in', $idList );

		$db->deleteTable($table);

		global $SQL_MASTER;
		$db = GMList::getDB($this->type);
		if($SQL_MASTER=='SQLiteDatabase') { $sql = 'DELETE FROM '.$this->type.'_delete;'; }
		else                              { $sql = 'TRUNCATE '.$this->type.'_delete;'; }
		$result = $db->sql_query( $sql );
	}

	
	/**
	 * データのうち既にIDが存在するもののみ処理。
	 * 処理したIDを返し新規データのみに絞れるようにする。
	 * 
	 * @param dataList 登録データリスト
	 * @param owner データのオーナー
	 * @return 登録したIDリスト
	 */
	function addRecordList( $dataList )
	{
		$db = GMList::getDB($this->type);

		$addIdList = array();
		if( count($dataList) == 0 ) { return $addIdList; }
		foreach( $dataList as $data )
		{
			$rec = $db->getNewRecord();
			$param = $this->createParam( $data );
			$this->setParam( $rec, $data, $param );
			$db->setData( $rec, 'shadow_id', (int)substr($data["id"],2) );
			$db->setData( $rec, 'id', $data["id"] );
			if( $this->isOwner ) { $db->setData( $rec, 'owner', $this->owner ); }
			$recList[] = $rec;

			$addIdList[] = $db->getData( $rec, $this->check_name );
		}
		if(method_exists( $db, 'addRecordList' )){
			$recUnit = array_chunk($recList, 300, true);
			foreach( $recUnit as $recList ) { $db->addRecordList( $recList, $this->id_update ); }
		}else{
			foreach( $recList as $rec) {
				$db->addRecord($rec);
			}
		}

		return $addIdList;
	}


	/**
	 * データのうち既にIDが存在するもののみ処理。
	 * 処理したIDを返し新規データのみに絞れるようにする。
	 * 
	 * @param dataList 編集対象を探すデータ配列
	 * @param idList 編集対象を探すID配列
	 * @return 編集したIDリスト
	 */
	function editRecordList( $dataList, $idList )
	{
		$db = GMList::getDB($this->type);

		$table = $db->getTable();
		if( $this->isOwner ) { $table = $db->searchTable( $table, 'owner', '=', $this->owner ); }
		$table = $db->searchTable( $table, $this->check_name, 'in', $idList );

		$editIdList = array();
		$row = $db->getRow( $table );
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$id = $db->getData( $rec, $this->check_name );
			$param = $this->createParam( $dataList[$id] );
			$this->setParam( $rec, $dataList[$id], $param );

			$db->updateRecord($rec);
			$editIdList[] = $id;
		}

		return $editIdList;
	}


	/**
	 * 登録するデータのみにする為、編集したIDを元にデータを削除
	 * 
	 * @param dataList 登録データのみ絞り込む配列
	 * @param idList 編集を行ったIDの配列
	 * @return 編集しなかったデータ配列
	 */
	function deleteDataList( $dataList, $idList )
	{
		foreach( $idList as $id ) { unset($dataList[$id]); }

		return $dataList;
	}

	
	/**
	 * 元データから新フォーマット用のデータを生成する
	 *
	 * @param base パラメータを生成する元データ
	 * @return 生成したデータ
	 */
	function createParam( $base )
	{
		$db = GMList::getDB($this->type);

		$dataList = array();

		// データを生成

		return $dataList;
	}

	/**
	 * 元データ、生成したデータをセットする
	 *
	 * @param rec パラメータをセットするレコード
	 * @param base 元データ
	 * @param param 新フォーマと用に生成されたデータ
	 */
	function setParam( &$rec, $base, $param )
	{
		$db = GMList::getDB($this->type);

		foreach( $this->gm->colName as $col )
		{// 同名カラムが存在する初期値をセット
			if( isset($base[$col]) && strlen($base[$col]) > 0 ) { $db->setData( $rec, $col, $base[$col] ); }
		}
		// 生成したデータをセット
		foreach( $param as $col => $value ){ $db->setData( $rec, $col, $value ); }

	}

	
}
