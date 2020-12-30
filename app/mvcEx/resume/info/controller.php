<?php

	//★クラス //

	/**
		@brief 既定の詳細情報ページのコントローラ。
	*/
	class AppresumeInfoController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 詳細情報表示要求への応答。
		*/
		function viewDetail() //
		{
			$this->model->verifyViewAuthority();

			if( $this->model->canView() ) //閲覧できる場合
			{
				$this->model->doQuickUpdate();

				$this->view->drawInfoPage( $this->model );
			}
			else //閲覧できない場合
				{ $this->view->drawErrorPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $loginUserType;
			global $LOGIN_ID;

			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			$this->action = 'viewDetail';

			parent::__construct();

            if($loginUserType == "cUser"){
                Concept::IsTrue(Conf::checkData("user", "use_nuser", "on"))->OrThrow("IllegalAccess");

				if(Conf::checkData("user", "disp_nuser", "off")){
					$db = GMList::getDB('entry');
					$table = $db->getTable();
					$table = $db->searchTable($table, 'items_owner', '=', $LOGIN_ID);
					$table = $db->searchTable($table, 'entry_user', '=', SystemUtil::getTableData($_GET['type'], $_GET['id'], 'owner'));
					$buf = $db->existsRow($table);
					Concept::IsTrue($buf)->OrThrow("IllegalAccess");
				}
                Concept::IsTrue(pay_jobLogic::isAvailable($LOGIN_ID, "mid") || pay_jobLogic::isAvailable($LOGIN_ID, "fresh"))->OrThrow("resumeContractExpire");
            }

        }
	}
