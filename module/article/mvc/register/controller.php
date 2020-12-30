<?php

	//★クラス //

	class ApparticleRegisterController extends AppRegisterController //
	{
		function __construct() //
		{
			global $gm;
			global $LOGIN_ID;
			global $loginUserType;
			global $ACTIVE_NONE;

			ConceptSystem::CheckPostMaxSizeOrver()->OrThrow( 'PostMaxSizeOrver' );
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableRegistUser()->OrThrow( 'IllegalAccess' );

			unset( $_SESSION[ 'previous_page' ] );
			unset( $_SESSION[ 'previous_page_admin' ] );

			if( !$this->action ) //要求処理が特定されていない場合
			{
				$isOver = SystemUtil::CheckTableRegistCount( $_GET[ 'type' ] );

				if( is_string( $isOver ) ) //登録上限1で超過している場合
					{ $this->action = 'goEdit'; }
				else if( $isOver ) //上限2以上で超過している場合
					{ $this->action = 'registerMaxCountOver'; }
				else if( isset( $_GET[ 'mode' ] ) && 'registMaxCountOver' == $_GET[ 'mode' ] ) //エラー表示要求がある場合
					{ $this->action = 'registerMaxCountOver'; }
				else if( isset( $_POST[ 'back' ] ) ) //前画面に戻る要求がある場合
				{
					if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
					{ header( 'Location: index.php?app_controller=Search&type=' . $_GET[ 'type' ] . '&run=true' ); }
					else
					{ header( 'Location: search.php?type=' . $_GET[ 'type' ] . '&run=true' ); }
				}
				else //要求がない場合
				{// レコードを追加して編集画面へ
					if( isset( $_POST[ 'post' ] ) ) //POSTクエリが送信されている場合
					{
						if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
						{ header( 'Location: index.php?app_controller=Search&type=' . $_GET[ 'type' ] . '&run=true' ); }
						else
						{ header( 'Location: search.php?type=' . $_GET[ 'type' ] . '&run=true' ); }
					}
					else //要求がない場合
					{
						$_db   = $gm[ $_GET[ 'type' ] ]->getDB();
						$ini = array(
							'owner'=>$LOGIN_ID,
							'owner_type'=>$loginUserType,
							'name'=>'名称無し',
							'activate'=> $ACTIVE_NONE,
							'regist'=>time(),
						);
						$_rec = $_db->getNewRecord( $ini );
						$_db->addRecord($_rec);
						$id = $_db->getData($_rec,'id');
						if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
						{ header( 'Location: index.php?app_controller=Edit&type=' . $_GET[ 'type' ] . '&id=' . $id ); }
						else
						{ header( 'Location: edit.php?type=' . $_GET[ 'type' ] . '&id=' . $id ); }
					}
				}
				parent::__construct();
			}
		}

	}
