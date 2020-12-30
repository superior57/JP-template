<?php

	//★クラス //

	/**
		@brief 既定の汎用データ検索フォームのビュー。
	*/
	class AppentrySearchView extends AppSearchView //
	{
		//■処理 //

		/**
			@brief     問い合わせフォームの画面を出力する。
			@param[in] $iModel モデルインスタンス。
		*/
		function doInquiry( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRegistForm( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     検索フォームの画面を出力する。
			@param[in] $iModel モデルインスタンス。
		*/
		function drawSearchFormPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawSearchForm( $iModel->search , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     検索結果画面を出力する。
			@param[in] $iModel モデルインスタンス。
		*/
		function drawSearchResultPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawSearch( $gm , $iModel->search , $iModel->table , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     検索結果なし画面を出力する。
			@param[in] $iModel モデルインスタンス。
		*/
		function drawSearchResultEmptyPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawSearchNotFound( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}
	}
