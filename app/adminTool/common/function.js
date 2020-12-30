(function(){

	var $PopupContainerHTML  = '<div class="popupContainer"><div class="backScreen">&nbsp;</div></div>';
	var $ReportContainerHTML = '<div class="reportContainer"><span>進行：<span class="reportProgressNum">0</span>％<br /></span><span class="resultArea"></span></div>';

	/**
		@brief     レポート出力領域を追加する。
		@param[in] $iElement イベント要素。
	*/
	function AddReportContainer( $iElement ) //
	{
		switch( $iElement.attr( 'data-report-type' ) ) //レポートの種類で分岐
		{
			case 'popup' : //ポップアップ
			default      : //その他
			{
				$( '.popupContainer' ).append( $ReportContainerHTML );

				break;
			}

			case 'target' : //指定の要素
			{
				$( $iElement.attr( 'data-report-target' ) ).append( $ReportContainerHTML );

				break;
			}
		}
	}

	/**
		@brief     レポートを開始する。
		@param[in] $iElement イベント要素。
	*/
	function BeginReport( $iElement ) //
	{
		switch( $iElement.attr( 'data-report-type' ) ) //レポートの種類で分岐
		{
			case 'popup' : //ポップアップ
			default      : //その他
			{
				$( 'body' ).append( $PopupContainerHTML );

				break;
			}

			case 'target' : //指定の要素
				{ break; }
		}
	}

	/**
		@brief     イベントを起動する。
		@param[in] $iElement イベント要素。
	*/
	function BootEvent( $iElement ) //
	{
		var $events = CreateEventQueue( $iElement );

		BeginReport( $iElement );

		if( !DoConfirm( $iElement ) ) //実行許可が得られなかった場合
		{
			CloseReport( $iElement );
			return;
		}

		AddReportContainer( $iElement );
		DoUpdateProgress( $events , $events.length );
		CallEvent( $iElement , $events , $events.length );
	}

	/**
		@brief     イベントキューを処理する。
		@param[in] $iElement    イベント要素。
		@param[in] $iEventQueue イベントキュー。
		@param[in] $iEventNum   イベントの最大数。
	*/
	function CallEvent( $iElement , $iEventQueue , $iEventNum ) //
	{
		var $element = $iEventQueue.shift();
		var $event   = $element[ 'event' ];
		var $table   = $element[ 'table' ];

		$.ajax({
			'url'      : 'tool.php' ,
			'type'     : 'POST' ,
			'dataType' : 'text' ,
			'data'     : { 'app_controller' : $event , 'type' : $table } ,
			'success'  : function( $iData , $iDataType ){ DoReport( $event , $table , $iData ); } ,
			'error'    : function( $iRequest , $iErrorCode , $iException ){ DoErrorReport( $event , $table , $iErrorCode ); } ,
			'complete' : function()
			{
				DoUpdateProgress( $iEventQueue , $iEventNum );

				if( $iEventQueue.length ) //キューが残っている場合
					{ CallEvent( $iElement , $iEventQueue , $iEventNum ); }
				else //キューが空の場合
					{ EndReport( $iElement ); }
			}
		});
	}

	/**
		@brief     レポートを閉じる。
		@param[in] $iElement イベント要素。
	*/
	function CloseReport( $iElement ) //
	{
		$( '.popupContainer' ).remove();
		$( '.reportContainer' ).remove();
	}

	/**
		@brief     イベントキューを作る。
		@param[in] $iElement イベント要素。
	*/
	function CreateEventQueue( $iElement ) //
	{
		var $events  = $iElement.attr( 'data-event' ).split( ',' );
		var $results = Array();

		for( var $i = 0 ; $events.length > $i ; ++$i ) //全ての要素を処理
		{
			var $data = $events[ $i ].split( ':' );

			$results.push( { 'event' : $data[ 0 ] , 'table' : $data[ 1 ] } );
		}

		return $results;
	}

	/**
		@brief     イベント実行の確認を取る。
		@param[in] $iElement イベント要素。
		@retval    true  許可が得られた場合。
		@retval    false 許可が得られない場合。
	*/
	function DoConfirm( $iElement ) //
	{
		if( !$iElement.attr( 'data-confirm' ) ) //確認が必要ない場合
			{ return true; }

		return window.confirm( $iElement.attr( 'data-confirm' ) );
	}

	/**
		@brief     通信エラーレポートを追加する。
		@param[in] $iEvent     イベント名。
		@param[in] $iTable     テーブル名。
		@param[in] $iErrorCode エラーコード。
	*/
	function DoErrorReport( $iEvent , $iTable , $iErrorCode ) //
	{
		var $container = $( '.reportContainer' );

		$container.append( '<div>' + $iTable + '：通信エラーが発生しました<br />この処理は実行されなかった可能性があります<br />(エラーコード：' + $iErrorCode + ')</div>' );
	}

	/**
		@brief     レポートを追加する。
		@param[in] $iEvent  イベント名。
		@param[in] $iTable  テーブル名。
		@param[in] $iResult 通信結果。
	*/
	function DoReport( $iEvent , $iTable , $iResult ) //
	{
		var $container = $( '.reportContainer .resultArea' );

		$container.prepend( $iResult );
	}

	/**
		@brief     進捗レポートを更新する。
		@param[in] $iEventQueue イベントキュー。
		@param[in] $iEventNum   イベントの最大数。
	*/
	function DoUpdateProgress( $iEventQueue , $iEventNum ) //
	{
		var $container = $( '.reportProgressNum' );

		$container.html( 100 - parseInt( ( $iEventQueue.length / $iEventNum ) * 100 ) );
	}

	/**
		@brief     レポートを終了する。
		@param[in] $iElement イベント要素。
	*/
	function EndReport( $iElement ) //
	{
		$( '.reportContainer' ).append( $iElement.attr( 'data-close-message' ) );
		$( '.reportContainer' ).prepend( $iElement.attr( 'data-close-message' ) );
	}

	/**
		@brief ツールチップを隠す。
		@param[in] $iElement マウスが離れた要素。
	*/
	function HideTooltip( $iElement ) //
	{
		var $tooltip = $( 'div.tooltip' );

		$tooltip.fadeOut( 100 , function(){ $tooltip.remove() } );
	}

	/**
		@brief イベントを初期化する。
	*/
	function Initialize() //
	{
		$( '[data-summary]' ).bind( 'mouseover' , function(){ ViewTooltip( $( this ) ); } );
	$( '[data-summary]' ).bind( 'mouseout'  , function(){ HideTooltip( $( this ) ); } );

		$( 'input[type="button"][data-event]' ).bind( 'click' , function(){ BootEvent( $( this ) ); } );

		var $bootTrigger = $( '[data-trigger="boot"]' );

		if( $bootTrigger.length ) //起動時イベントがある場合
			{ BootEvent( $bootTrigger ); }
	}

	/**
		@brief     ツールチップを表示する。
		@param[in] $iElement マウスを置かれた要素。
	*/
	function ViewTooltip( $iElement ) //
	{
		var $left  = $iElement.position().left;
		var $top   = $iElement.position().top;
		var $width = $iElement.width();

		$iElement.before( '<div class="tooltip" style="left:' + ( $left - 150 + ( $width / 2 ) ) + 'px;top:' + ( $top + 50 ) + 'px;">' + $iElement.attr( 'data-summary' ) + '</div>' );
	}

	$( Initialize );

})();
