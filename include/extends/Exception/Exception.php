<?php

	if( !class_exists( 'BadFunctionCallException' ) ) //SPL例外クラスが見つからない場合
		{ include_once( 'include/extends/Exception/SubException.php' ); }

	/**
		@brief   例外オブジェクト。
		@details 不正なクエリパラメータを受け取った場合にスローされます。\n
				この例外をキャッチした場合は、操作が受け付けられない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class InvalidQueryException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 不正なアクセスが発生した場合にスローされます。\n
				この例外をキャッチした場合は、アクセス権限がない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class IllegalAccessException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 不正なアクセスが発生した場合にスローされます。\n
				この例外をキャッチした場合は、アクセストークンに問題がある旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class IllegalTokenAccessException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details ファイルの入出力に失敗した場合にスローされます。\n
				この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class FileIOException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details データベースの更新に失敗した場合にスローされます。\n
				この例外をキャッチした場合は、操作が適用されなかった可能性がある旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class UpdateFailedException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 何らかの理由で画面出力に失敗した場合にスローされます。\n
				この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class OutputFailedException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details コマンドコメントのパラメータに不正な値が指定された場合にスローされます。\n
				この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class InvalidCCArgumentException extends LogicException
	{}

	/**
		@brief   例外オブジェクト。
		@details アップロードされたファイルがpost_max_sizeをオーバーしていた場合にスローされます。。\n
				この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class PostMaxSizeOrverException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 不正なアクセスが発生した場合にスローされます。\n
				この例外をキャッチした場合は、アクセス対象のデータが存在しない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class RecordNotFoundException extends RuntimeException
	{}
	
	/**
	 @brief   例外オブジェクト。
	 @details 内部処理で問題が発生した場合にスローされます。\n
	 この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
	 @ingroup Exception
	 */
	class InternalErrorException extends RuntimeException
	{}
	
	//実装

	class HasLimitsException extends RuntimeException
	{}

	class NotExistsResumeException extends RuntimeException
	{}

	class userDataUneditedException extends RuntimeException
	{}

	class freshContractExpireException extends RuntimeException
	{}

	class midContractExpireException extends RuntimeException
	{}

	class entryContractExpireException extends RuntimeException
	{}

	class messageContractExpireException extends RuntimeException
	{}

	class resumeContractExpireException extends RuntimeException
	{}

	class resumeAllDeleteException extends RuntimeException
	{}

	class ExistsApplyException extends RuntimeException
	{}

	class cantSendMessageException extends RuntimeException
	{}

	class unDefinedUserException extends RuntimeException
	{}

	class noAuthorityScoutException extends RuntimeException
	{}

	class expiredJobException extends RuntimeException
	{}

	class nonPeriodicException extends RuntimeException
	{}

	class interviewContractExpireException extends RuntimeException
	{}

	class expiredInterviewException extends RuntimeException
	{}

	class NotExistsBankAccountException extends RuntimeException
	{}