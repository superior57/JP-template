<?php

	/**
		@file
		@brief SPLの標準例外がない環境で同名の例外を使うための代替定義。
	*/

	/**
		@brief   例外オブジェクト。
		@details 未定義の関数をコールバックが参照したり、引数を指定しなかったりした場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class BadFunctionCallException extends LogicException
	{}

	/**
		@brief   例外オブジェクト。
		@details 未定義のメソッドをコールバックが参照したり、引数を指定しなかったりした場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class BadMethodCallException extends BadFunctionCallException
	{}

	/**
		@brief   例外オブジェクト。
		@details 定義したデータドメインに値が従わないときにスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class DomainException extends LogicException
	{}

	/**
		@brief   例外オブジェクト。
		@details 引数が期待値に一致しなかった場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class InvalidArgumentException extends LogicException
	{}

	/**
		@brief   例外オブジェクト。
		@details 長さが無効な場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class LengthException extends LogicException
	{}

	/**
		@brief   例外オブジェクト。
		@details 論理式が無効な場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class LogicException extends Exception
	{}

	/**
		@brief   例外オブジェクト。
		@details 値が有効なキーでなかった場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class OutOfBoundsException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 値が範囲内におさまらなかった場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class OutOfRangeException extends LogicException
	{}

	/**
		@brief   例外オブジェクト。
		@details いっぱいになっているコンテナに要素を追加した場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class OverflowException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 無効な範囲が渡された場合にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class RangeException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details 実行時にだけ発生するようなエラーの際にスローされます。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class RuntimeException extends Exception
	{}

	/**
		@brief   例外オブジェクト。
		@details 空のコンテナから要素を削除しようとした際にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class UnderflowException extends RuntimeException
	{}

	/**
		@brief   例外オブジェクト。
		@details いくつかの値のセットに一致しない値であった際にスローされる例外です。\n
		         この例外をキャッチした場合は、現在システムが利用できない旨をユーザーに通知しなければいけません。
		@ingroup Exception
	*/
	class UnexpectedValueException extends RuntimeException
	{}

?>