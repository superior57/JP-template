<?php

	/**
	 * システムコールクラス
	 *
	 * @author ----
	 * @version 1.0.0
	 *
	 */
	class pageSystem extends System
	{
		/**********************************************************************************************************
		 * 汎用システム用メソッド
		 **********************************************************************************************************/

		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// 登録関係
		/////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * 登録内容確認。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param edit 編集なのか、新規追加なのかを真偽値で渡す。
		 * @return エラーがあるかを真偽値で渡す。
		 */
		function registCheck( &$gm, $edit, $loginUserType, $loginUserRank , $check = false)
		{
			$result	 = parent::registCheck( $gm, $edit, $loginUserType, $loginUserRank, $check );

			if($result){
				$db = $gm['page']->getDB();
				$table = $db->getTable();
				$table = $db->searchTable( $table, 'name', '=', $_POST['name'] );

				$authority = $_POST[ 'authority' ];
				if( !is_array( $_POST[ 'authority' ] ) ) { $authority = explode( '/' , $_POST['authority'] ); }

				foreach( $authority as $auth ){
					$table_buf[] = $db->searchTable( $db->getTable(), 'authority', 'in', '%'.$auth.'%' );
				}

				$table2 = $db->getTable();
				foreach($table_buf as $table_auth){
					$table2 = $db->orTable($table2,$table_auth);
				}

				$table = $db->andTable($table,$table2);

				if($edit){
					$table = $db->searchTable( $table, 'id', '!', $_POST['id'] );
				}

				if($db->existsRow($table)){
					System::$checkData->addError('name_dup');
					$result = false;
				}
			}
			return $result;
		}

		/**
		 * 登録処理完了処理。
		 * 登録完了時にメールで内容を通知したい場合などに用います。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec レコードデータ。
		 */
		function registComp( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $page_path;
			global $mobile_flag;
			global $sp_flag;
			global $FileBase;
			// **************************************************************************************

			$db = $gm['page']->getDB();
			$new_id = $db->getData( $rec , 'id' );

			SystemUtil::fileWrite( $FileBase->getfilepath($page_path.$new_id.".dat") , $_POST['html'] );

			if( $mobile_flag ){
				SystemUtil::fileWrite( $FileBase->getfilepath($page_path.$new_id.".mob.dat") , $_POST['mobile'] );
			}

			if( $sp_flag ){
				SystemUtil::fileWrite( $FileBase->getfilepath($page_path.$new_id.".sp.dat") , $_POST['smartphone'] );
			}
			parent::registComp( $gm, $rec, $loginUserType, $loginUserRank );
		}


		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// 編集関係
		/////////////////////////////////////////////////////////////////////////////////////////////////////////



		/**
		 * 編集前段階処理。
		 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec フォームのからの入力データを反映したレコードデータ。
		 */
		function editProc( &$gm, &$rec, $loginUserType, $loginUserRank ,$check = false)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			$db = $gm[$_GET['type']]->getDB();

			$db->setData(  $rec, 'updates',			time()  );
			parent::editProc( $gm, $rec, $loginUserType, $loginUserRank, $check );
		}



		/**
		 * 編集完了処理。
		 * フォーム入力以外の方法でデータを登録する場合は、ここでレコードに値を代入します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec フォームのからの入力データを反映したレコードデータ。
		 */
		function editComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $page_path;
			global $mobile_flag;
			global $sp_flag;
			global $FileBase;
			// **************************************************************************************

			$db       = $gm[ 'page' ]->getDB();
			$id       = $db->getData( $rec , 'id' );
			$pageName = $db->getData( $rec , 'name' );
			$oldName  = $db->getData( $old_rec , 'name' );

			if ($FileBase->file_exists($page_path . $oldName . ".dat")) {
				SystemUtil::fileDelete($FileBase->getfilepath($page_path . $oldName . ".dat"));
			}
			SystemUtil::fileWrite( $FileBase->getfilepath($page_path.$id.".dat") , $_POST['html'] );

			if( $mobile_flag ){
				if ($FileBase->file_exists($page_path . $oldName . ".mob.dat")) {
					SystemUtil::fileDelete($FileBase->getfilepath($page_path . $oldName . ".mob.dat"));
				}
				SystemUtil::fileWrite( $FileBase->getfilepath($page_path.$id.".mob.dat") , $_POST['mobile'] );
			}

			if( $sp_flag ){
				if ($FileBase->file_exists($page_path . $oldName . ".sp.dat")) {
					SystemUtil::fileDelete($FileBase->getfilepath($page_path . $oldName . ".sp.dat"));
				}
				SystemUtil::fileWrite( $FileBase->getfilepath($page_path.$id.".sp.dat") , $_POST['smartphone'] );
			}

			parent::editComp( $gm, $rec, $old_rec, $loginUserType, $loginUserRank );
		}


		function deleteProc(&$gm, &$rec, $loginUserType, $loginUserRank)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $page_path;
			global $mobile_flag;
			global $sp_flag;
			global $FileBase;
			// **************************************************************************************

			$db       = $gm[ 'page' ]->getDB();
			$id       = $db->getData( $rec , 'id' );
			$pageName = $db->getData( $rec , 'name' );

			parent::deleteProc($gm, $rec, $loginUserType, $loginUserRank);

			if ($FileBase->file_exists($page_path . $pageName . ".dat")) {
				SystemUtil::fileDelete($FileBase->getfilepath($page_path . $pageName . ".dat"));
			}
			if($FileBase->file_exists($page_path . $id . ".dat")) {
				SystemUtil::fileDelete($FileBase->getfilepath($page_path.$id.".dat" ));
			}

			if( $mobile_flag ){
				if ($FileBase->file_exists($page_path . $pageName . ".mob.dat")) {
					SystemUtil::fileDelete($FileBase->getfilepath($page_path . $pageName . ".mob.dat"));
				}
				if($FileBase->file_exists($page_path . $id . ".mob.dat")) {
					SystemUtil::fileDelete( $FileBase->getfilepath($page_path.$id.".mob.dat") );
				}
			}

			if( $sp_flag ){
				if ($FileBase->file_exists($page_path . $pageName . ".sp.dat")) {
					SystemUtil::fileDelete($FileBase->getfilepath($page_path . $pageName . ".sp.dat"));
				}
				if($FileBase->file_exists($page_path . $id . "sp.dat")) {
					SystemUtil::fileDelete( $FileBase->getfilepath($page_path.$id."sp.dat") );
				}
			}
		}




		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// 編集関係
		/////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * 詳細情報ページを描画する。
		 */
		function drawRegistForm( &$gm, $rec, $loginUserType, $loginUserRank )
		{
			header('X-XSS-Protection: 0');

			if(class_exists("mod_special") && ($_GET["mode"]=="special" || $_POST["mode"]=="special")) $MODE="_SP";

			if(  !isset( $_POST['post'] ) || !strlen($_POST['post']) )
			{// テンプレートの読み込み
				$_POST['html'] = SystemUtil::fileRead(Template::getTemplate( $loginUserType, $loginUserRank, 'page' , 'TEMPLATE_DESIGN'.$MODE ));
				$_POST['mobile'] = SystemUtil::fileRead(Template::getTemplate( $loginUserType, $loginUserRank, 'page_mobile' , 'TEMPLATE_DESIGN'.$MODE ));
				$_POST['smartphone'] = SystemUtil::fileRead(Template::getTemplate( $loginUserType, $loginUserRank, 'page_sp' , 'TEMPLATE_DESIGN'.$MODE ));
			}

			$this->setErrorMessage($gm[ $_GET['type'] ]);

			// 汎用処理
			if($gm[$_GET['type']]->maxStep >= 2){
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_FORM_PAGE_DESIGN'.$MODE . $_POST['step'] , 'index.php?app_controller=register&type='. $_GET['type'] );
			}else{
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_FORM_PAGE_DESIGN'.$MODE , 'index.php?app_controller=register&type='. $_GET['type'] );
			}
		}

		/**
		 * 登録内容確認ページを描画する。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 登録情報を格納したレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function drawRegistCheck( &$gm, $rec, $loginUserType, $loginUserRank )
		{
			header('X-XSS-Protection: 0');

			if(class_exists("mod_special") && ($_GET["mode"]=="special" || $_POST["mode"]=="special")) $MODE="_SP";

			if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]; }
			else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]; }
			else
				{ $action = ' '; }

			switch(  $_GET['type']  )
			{
				default:
					// 汎用処理
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_CHECK_PAGE_DESIGN'.$MODE , $action );
			}
		}

		function drawRegistComp( &$gm, $rec, $loginUserType, $loginUserRank )
		{
			header('X-XSS-Protection: 0');
			parent::drawRegistComp($gm, $rec, $loginUserType, $loginUserRank);
		}

		/**
		 * 編集フォームを描画する。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function drawEditForm( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $EDIT_FORM_PAGE_DESIGN;
			global $LOGIN_ID;
			global $NOT_LOGIN_USER_TYPE;
			global $mobile_flag;
			global $sp_flag;

			global $page_path;
			global $FileBase;
			// **************************************************************************************
			header('X-XSS-Protection: 0');

			$this->setErrorMessage($gm[ $_GET['type'] ]);

			$db		 = $gm[ 'page' ]->getDB();
			$id = $db->getData( $rec , 'id' );
			$pageName = $db->getData( $rec , 'name' );

			$idPath = $page_path.$id.".dat";
			$namePath = $page_path.$pageName.".dat";

			if($FileBase->file_exists($idPath)) {
				$_POST['html'] = SystemUtil::fileRead( $idPath );
			} else if($FileBase->file_exists($namePath)) {
				$_POST['html'] = SystemUtil::fileRead( $namePath );
			}

			if( $mobile_flag ){
				$idPath = $page_path.$id.".mob.dat";
				$namePath = $page_path.$pageName.".mob.dat";

				if($FileBase->file_exists($idPath)) {
					$_POST['mobile'] = SystemUtil::fileRead( $idPath );
				} else if($FileBase->file_exists($namePath)) {
					$_POST['mobile'] = SystemUtil::fileRead( $namePath );
				}
			}

			if( $sp_flag ){
				$idPath = $page_path.$id.".sp.dat";
				$namePath = $page_path.$pageName.".sp.dat";

				if($FileBase->file_exists($idPath)) {
					$_POST['smartphone'] = SystemUtil::fileRead( $idPath );
				} else if($FileBase->file_exists($namePath)) {
					$_POST['smartphone'] = SystemUtil::fileRead( $namePath );
				}
			}

			if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else
				{ $action = ' '; }

			$this->setErrorMessage( $gm[ $_GET['type'] ] );

			if(class_exists("mod_special") && $db->getData($rec,"mode")=="special") $MODE="_SP";

			switch(  $_GET['type']  )
			{
				default:
					// 汎用処理
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_FORM_PAGE_DESIGN'.$MODE , $action , Template::getOwner() );
			}

		}

		/**
		 * 編集内容確認ページを描画する。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function drawEditCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			header('X-XSS-Protection: 0');

			if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else
				{ $action = ' '; }

			if(class_exists("mod_special") && ($_GET["mode"]=="special" || $_POST["mode"]=="special")) $MODE="_SP";

			switch(  $_GET['type']  )
			{
				default:
					// 汎用処理
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_CHECK_PAGE_DESIGN'.$MODE , $action , Template::getOwner() );
			}
		}

		function drawEditComp( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			header('X-XSS-Protection: 0');
			parent::drawEditComp($gm, $rec, $loginUserType, $loginUserRank);
		}

		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// 削除関係
		/////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * 削除編集フォームを描画する。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function drawDeleteForm( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=delete&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=delete&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else
				{ $action = ' '; }

			$this->setErrorMessage($gm[ $_GET['type'] ]);

			$db		 = $gm[ 'page' ]->getDB();
			if(class_exists("mod_special") && $db->getData($rec,"mode")=="special") $MODE="_SP";

			switch(  $_GET['type']  )
			{
				default:
					// 汎用処理
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'DELETE_FORM_PAGE_DESIGN'.$MODE , $action , Template::getOwner() );
			}
		}

		/**
		 * 削除確認ページを描画する。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param rec 編集対象のレコードデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function drawDeleteCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
		{
			if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=delete&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ $action = 'index.php?app_controller=delete&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]; }
			else
				{ $action = ' '; }

			if(class_exists("mod_special") && ($_GET["mode"]=="special" || $_POST["mode"]=="special")) $MODE="_SP";

			switch(  $_GET['type']  )
			{
				default:
					// 汎用処理
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'DELETE_CHECK_PAGE_DESIGN'.$MODE , $action , Template::getOwner() );
			}
		}


		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// 検索関係
		/////////////////////////////////////////////////////////////////////////////////////////////////////////



		function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){

			SearchTableStack::pushStack($table);

			/*		一括メール送信へリダイレクト		*/
			if( isset( $_GET[ 'multimail' ] ) ){
				$db		= $gm[ $_GET[ 'type' ] ]->getDB();
				$row	= $db->getRow( $table );

				for( $i=0 ; $i<$row ; $i++ ){
					$rec	 = $db->getRecord( $table, $i );
					$_GET['pal'][] = $db->getData( $rec, 'id' );
				}
				$_GET['type'] = 'multimail';

				if( is_array( $_GET[ 'pal' ] ) ){
					Header( 'Location: index.php?app_controller=register&type=multimail&pal[]=' . implode( '&pal[]=' , $_GET[ 'pal' ] ) );
				}else{
					Header( 'Location: index.php?app_controller=register&type=multimail' );
				}
			}else{
				if(class_exists("mod_special")) $MODE="_SP";

				$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_RESULT_DESIGN'.$MODE );

				if( strlen($file) ){
					$sr->addHiddenForm('type',$_GET['type']);
					print $sr->getFormString( $file , 'search.php' , null , 'v' );
				}else{
					Template::drawErrorTemplate();
				}
			}
		}

		/**
		 * 検索結果をリスト描画する。
		 * ページ切り替えはこの領域で描画する必要はありません。
		 *
		 * @param gm GUIManagerオブジェクト
		 * @param table 検索結果のテーブルデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function getSearchResult( &$_gm, $table, $loginUserType, $loginUserRank )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $gm;
			// **************************************************************************************

			$type = SearchTableStack::getType();
			if(class_exists("mod_special")) $MODE="_SP";

			switch( $type )
			{
				default:
					if(SearchTableStack::getPartsName('list'))
					$html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $type , 'SEARCH_LIST_PAGE_DESIGN'.$MODE , false , SearchTableStack::getPartsName('list') );
					else
					$html = Template::getListTemplateString( $gm[ $type ] , $table , $loginUserType , $loginUserRank , $type , 'SEARCH_LIST_PAGE_DESIGN'.$MODE );
					break;
			}

			return $html;
		}

		/**
		 * 検索結果、該当なしを描画。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function drawSearchNotFound( &$gm, $loginUserType, $loginUserRank )
		{
			Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_NOT_FOUND_DESIGN' );
		}

	}

