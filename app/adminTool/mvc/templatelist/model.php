<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのテンプレート一覧表示処理のモデル。
	*/
	class AppTemplateListModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief テンプレートの一覧を取得する。
		*/
		function getTemplateList() //
		{
			global $ACTIVE_NONE;
			global $ACTIVE_ACTIVATE;
			global $ACTIVE_ACCEPT;
			global $ACTIVE_DENY;
			global $template_path;

			$statement = DB::Query( 'SELECT * FROM template ORDER BY target_type ASC , label ASC' );

			foreach( $statement as $row ) //全ての行を処理
			{
				$users = explode( '/' , $row[ 'user_type' ] );

				array_shift( $users );
				array_pop( $users );

				foreach( $users as $user ) //全てのユーザー種別を処理
				{
					if( !$list[ $user ][ $row[ 'target_type' ] ] ) //ラベル配列が未作成の場合
					{
						$list[ $user ][ $row[ 'target_type' ] ] = Array(
							'HEAD_DESIGN'              => Array() ,
							'TOP_PAGE_DESIGN'          => Array() ,
							'FOOT_DESIGN'              => Array() ,
							'REGIST_FORM_PAGE_DESIGN'  => Array() ,
							'REGIST_CHECK_PAGE_DESIGN' => Array() ,
							'REGIST_COMP_PAGE_DESIGN'  => Array() ,
							'REGIST_ERROR_DESIGN'      => Array() ,
							'EDIT_FORM_PAGE_DESIGN'    => Array() ,
							'EDIT_CHECK_PAGE_DESIGN'   => Array() ,
							'EDIT_COMP_PAGE_DESIGN'    => Array() ,
							'DELETE_CHECK_PAGE_DESIGN' => Array() ,
							'DELETE_COMP_PAGE_DESIGN'  => Array() ,
							'SEARCH_FORM_PAGE_DESIGN'  => Array() ,
							'SEARCH_RESULT_DESIGN'     => Array() ,
							'SEARCH_NOT_FOUND_DESIGN'  => Array() ,
							'SEARCH_LIST_PAGE_DESIGN'  => Array() ,
							'INFO_PAGE_DESIGN'         => Array() ,
							'INCLUDE_DESIGN'           => Array()
						);
					}

					foreach( Array( $ACTIVE_NONE , $ACTIVE_ACTIVATE , $ACTIVE_ACCEPT , $ACTIVE_DENY ) as $activate ) //全てのアクティベートレベルを処理
					{
						if( $row[ 'activate' ] & $activate ) //アクティベートレベルが一致する場合
						{
							if( !is_file( PathUtil::ModifyTemplateFilePath( $row[ 'file' ] ) ) ) //ファイルが存在しない場合
								{ $list[ $user ][ $row[ 'target_type' ] ][ $row[ 'label' ] ][ $activate ] = Array( 'path' => $row[ 'file' ] , 'status' => 'not found' ); }
							else if( 0 === filesize( PathUtil::ModifyTemplateFilePath( $row[ 'file' ] ) ) ) //ファイルが空の場合
								{ $list[ $user ][ $row[ 'target_type' ] ][ $row[ 'label' ] ][ $activate ] = Array( 'path' => $row[ 'file' ] , 'status' => 'empty file' ); }
							else //ファイルが正常な場合
								{ $list[ $user ][ $row[ 'target_type' ] ][ $row[ 'label' ] ][ $activate ] = Array( 'path' => $row[ 'file' ] , 'status' => 'success' ); }
						}
						else //アクティベートレベルが一致しない場合
							{ $list[ $user ][ $row[ 'target_type' ] ][ $row[ 'label' ] ][ $activate ] = null; }
					}
				}
			}

			ksort( $list );

			$this->templateList = $list;
		}

		var $templateList = Array(); ///<テンプレート配列。
	}
