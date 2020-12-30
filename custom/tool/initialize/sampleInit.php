<?php

require_once './include/base/Initialize.php';

//ショッピングモールで使っている物をsampleとして置いておく。

class category_countInit implements Initialize{
	public function initTable(){
			
		$ccDB = GMList::getDB('category_count');
		
		$ccDB->deleteTable( $ccDB->getTable() );
		print '<p>カテゴリーカウントテーブルをクリアしました。</p>';
			
		$categorys = array(
			array(
				'name'=>'parentCategory',
				'column'=>'parent'
			),
			array(
				'name' => 'childCategory',
				'column'=>'child'
			)
		);
		$time = time();
		$count =0;
			
		foreach( $categorys as $category ){
		
			$cDB = GMList::getDB($category['name']);
			$ptable = $cDB->getTable();
			$ptable = $cDB->searchTable( $ptable , 'open' , '=' , true );
			$stable = $cDB->joinTableLike($ptable,$category['name'],'item','id',$category['column']);
		
			$stable = $cDB->getCountTable($cDB->prefix.strtolower($category['name']).'.id,item.owner',$stable);
		
			$stable = $cDB->joinTableSearch( GMList::getDB('item') ,$stable ,'open', '=', true);
		
			$row = $cDB->getRow($stable);
			for($i=0;$i<$row;$i++){
				$rec = $cDB->getRecord($stable,$i);
				$list[ $cDB->getData($rec,'id') ] = $cDB->getData($rec,'cnt');
				$nRec = $ccDB->getNewRecord();
				$ccDB->setData( $nRec, 'category_id',$cDB->getData($rec,'id') );
				$ccDB->setData( $nRec, 'owner_id',$cDB->getData($rec,'item.owner') );
				$ccDB->setData( $nRec, 'count', $cDB->getData($rec,'cnt'));
				$ccDB->setData( $nRec, 'regist',$time );
				$ccDB->addRecord( $nRec );
				$count++;
			}
		}
		print '<p>'.$count.'件のカテゴリーに対して、アイテムのカウント数を設定しました。</p>';
		print '<p>カテゴリーカウントテーブルの初期化に成功しました。</p>';
	}
}