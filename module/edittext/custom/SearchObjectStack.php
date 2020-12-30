<?php
/**
 * Searchableを実装したオブジェクト専用のスタッククラス
 */
class SearchObjectStack {
    private $stackList = array();
    private $maxCount = 0;
    function __construct($stack) {
        $this->push($stack);
    }
    public function push($stack) {
        $this->checkInstance($stack);
        $this->maxCount++;
        $this->stackList[] = $stack;
    }
    public function pop() {
        $this->checkEmpty();
        $this->maxCount--;
        return array_pop($this->stackList);
    }
    public function first() {
        $this->checkEmpty();
        return $this->stackList[0];
    }
    public function last() {
        $this->checkEmpty();
        return $this->stackList[$this->size() - 1];
    }
    public function index($i) {
        $this->checkEmpty();
        $this->checkIndex($i);
        return $this->stackList[$i];
    }

    protected function checkIndex($i) {
        if($i >= 0 && $i < $this->size()) {
            throw new EditTextException('インデックスが範囲外です。');
        }
    }
    protected function checkEmpty() {
        if($this->isEmpty()) {
            throw new EditTextException('スタックが空になっています。');
        }
    }
    protected function checkInstance($stack) {
        if(!($stack instanceof Searchable)) {
            throw new EditTextException('Searchableが実装されたクラスのインタスタンスが指定されていません。');
        }
    }

    public function size() {
        return $this->maxCount;
    }

    public function isEmpty() {
        return $this->size() == 0;
    }

    /**
     * 渡されたオブジェクトと文字列をを解析して自身の値を返します
     * @param mixed $stack
     * @param array $args
     * @param boolean $lastArrayFlg trueにすると最終的な値が配列であった場合その値を返します。
     * @return mixed
     */
    public function getValueBySyntax($syntax) {
        $syntaxList = $this->getSyntaxList($syntax);
        $stack = $this->searchStack($this->last(), $syntaxList, $lastArrayFlg);
        return $this->getString($stack);
    }

    /**
     * 渡されたオブジェクトと配列を解析して値を返します
     * @param mixed $stack
     * @param array $args
     * @return mixed 値が見つからない場合はnullを返す
     */
    private function searchStack($stack, array $args) {
        $syntax = array_shift($args);
        $counter = count($args);
        $index = null;
        if(preg_match('/(\w+)\[(.+?)\]$/', $syntax, $matches)) {
            $syntax = $matches[1];
            $index = $matches[2];
        }
        
        $stack = $this->getValue($stack, $syntax);
        if(is_array($stack)) {
            if(!is_null($index)) {
                if(isset($stack[$index])) {
                    $stack = $stack[$index];
                } else {
                    return null;
                }
            }
        }

        if($counter) {
            return $this->searchStack($stack, $args);
        } else {
            return $stack;
        }
    }

    /**
     * Searchableが実装されたクラスのオブジェクトからプロパティを取り出す
     */
    private function getValue($stack, $syntax) {
        if(!($stack instanceof Searchable)) {
            throw new EditTextException('不正な値が渡されています。');
        }
        return $stack->getValue($syntax);
    }

    /**
     * Searchableが実装されたクラスのオブジェクトから文字列を取り出す
     * getValueBySyntaxで指定した値が最終的にSearchableだった場合は
     * toStringの文字列を返す
     */
    private function getString($stack) {
        if($stack instanceof Searchable) {
            return $stack->toString();
        }
        return $stack;
    }

    /**
     * 文字列を分解してgetStackが読み取れる文字列配列に変換します
     * @param string $syntax
     * @param array
     */
    private function getSyntaxList($syntax) {
        $result = array();
        $syntax = str_replace(array('"', "'"), '', $syntax);
        if(strpos($syntax, '->')) {
            $result = explode('->', $syntax);
        } else {
            $result = array( $syntax );
        }
        return $result;
    }

    private static $stack = null;
    /**
     * @param SearchObjectStack $stack
     */
    public function setStack($stack) {
        self::$stack = new SearchObjectStack($stack);
    }

    /**
     * @return SearchObjectStack
     */
    public function getStack() {
        if(is_null(self::$stack)) {
            throw new EditTextException('スタックが設定されていません。');
        }
        return self::$stack;
    }
}