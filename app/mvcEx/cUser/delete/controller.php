<?php

	//★クラス //

	/**
		@brief 既定のデータ削除処理のコントローラ。
	*/
	class AppcUserDeleteController extends AppDeleteController //
	{

		function noResign(){
			$this->view->drawNoResignPage($this->model);
		}

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $LOGIN_ID;

			if( !$this->action ) //要求処理が特定されていない場合
				{ $this->action = ( isset( $_GET[ 'app_action' ] ) ? $_GET[ 'app_action' ] : 'index' ); }

			$modelName = preg_replace( '/^App(\w+)Controller$/' , 'App$1Model' , get_class( $this ) );

			if( !$this->model ) //modelが生成されていない場合
				{ $this->model = new $modelName(); }

			$viewName = preg_replace( '/^App(\w+)Controller$/' , 'App$1View' , get_class( $this ) );

			if( !$this->view ) //modelが生成されていない場合
				{ $this->view = new $viewName(); }


			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableNoHTML()->OrThrow( 'IllegalAccess' );

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			$canResign = cUserLogic::canResign($LOGIN_ID);

			if($canResign){
				if( $_REQUEST[ 'del_edit' ] ) //入力フォームを使用する場合
				{
					if( isset( $_POST[ 'back' ] ) ) //前画面に戻る要求がある場合
						{ $this->action = 'goBack'; }
					else //前画面に戻る要求がない場合
					{
						if( isset( $_POST[ 'post' ] ) ) //POSTクエリが送信されている場合
						{
							if( 'delete' == $_POST[ 'post' ] ) //削除実行処理が要求されている場合
								{ $this->action = 'doDelete'; }
							else //要求がない場合
								{ $this->action = 'confirmDelete'; }
						}
						else //POSTクエリが送信されていない場合
							{ $this->action = 'deleteForm'; }
					}
				}
				else //入力フォームを使用しない場合
				{
					if( 'delete' == $_POST[ 'post' ] ) //削除実行処理が要求されている場合
						{ $this->action = 'doDelete'; }
					else //要求がない場合
						{ $this->action = 'confirmDeletePage'; }
				}
			}else{
				$this->action = 'noResign';
			}

		}
	}
