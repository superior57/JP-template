<?php

class mod_edittext extends command_base
{
    /**
     * SearchValueManagerでsaveしたtypeでオブジェクトをロードして変換テキストを出力
     */
    public function drawMakeText(&$gm, $rec, $args) {
        if(isset($args[0])) {
            $type = $args[0];
        } else {
            return;
        }

        $svm = SearchValueManager::load($type);
        if(is_null($svm)) {
            return;
        }
        $addstr = $this->getAddString($type, $gm, $svm);
		$this->addBuffer($svm->getString($_GET, $addstr));
    }

    /**
     * 取り出されるtype名によって追加するテキスト処理を分岐させる
     */
    private function getAddString($type, $gm, $svm) {
        $addstr = '';
        switch($type) {
            case 'mid_title':
            case 'mid_desc':
            default:
                $row = $gm->getVariable('RES_ROW') ? $gm->getVariable('RES_ROW') : 0;
                $addstr = $row >= $svm->getOther('rowNum') ? sprintf($svm->getOther('addText'), $row) : '';
                break;
        }
        return $addstr;
    }

    /* ------------ 以下other.phpで使うCC --------------------*/
    function drawURI(&$gm, $rec, $args) {
        $this->addBuffer($_SERVER["REQUEST_URI"]);
    }

    function drawFileList(&$gm, $rec, $args) {
		global $loginUserType;
		global $loginUserRank;

		$design = Template::getTemplate($loginUserType, $loginUserRank, self::$type, "EDIT_TEXT_FILE_LIST");

        $fileList = SearchValueManager::getTypeList();
        if($fileList !== false && count($fileList)) {
            $buffer = $gm->getString($design, null, 'head');
            foreach($fileList as $type) {
                $_rec = array();
    
                $path = SearchValueManager::getFilePath($type);
                $gm->setVariable('path', $path);
    
                $time =  filemtime($path);
                $gm->setVariable('time', $time);
    
                $size = filesize($path);
                $gm->setVariable('size', $size);

                $gm->setVariable('type', $type);
    
                $buffer .= $gm->getString($design, null, 'list');
            }
            $buffer .= $gm->getString($design, null, 'foot');
        } else {
            $buffer = $gm->getString($design, null, 'faled');
        }
		$this->addBuffer($buffer);
    }

    /**
     * このCCはSearchObjectStack::setStackでセットされたオブジェクトのプロパティを表示
     * プロパティのして方法はObjectやObject->nameやObject->list[0]です
     * オブジェクト型のものはSearchableを実装していないクラスのオブジェクト以外はエラーとなります
     * <!--# mod edittext v プロパティ #-->
     */
    function v(&$gm, $rec, $args) {
        if(!isset($args[0])) {
            throw new Exception('CCの引数が足りません。');
        }
        $syntax = $args[0];

        $stack = SearchObjectStack::getStack();
        $buffer = $stack->getValueBySyntax($syntax);
        $this->addBuffer($buffer);
    }

    /**
     * このCCはSearchObjectStack::setStackでセットされたオブジェクトの配列プロパティを表示
     * プロパティのして方法はObjectやObject->nameやObject->list[0]です
     * 配列でないもの以外を指定するとエラーとなります
     * <!--# mod edittext each 配列プロパティ readheadラベル名 #-->
     */
    function each(&$gm, $rec, $args) {
        if(count($args) < 2) {
            throw new Exception('CCの引数が足りません。');
        }
        List($syntax, $label) = $args;

        $stack = SearchObjectStack::getStack();
        $list = $stack->getValueBySyntax($syntax);
        if(!is_array($list)) {
            throw new Exception('配列が指定されていません。');
        }

        foreach($list as $key => $val) {
            $stack->push($val);
            $gm->setVariable('key', $key);
            $buffer .= $gm->getCCResult( $rec , '<!--# adapt ' . $label . ' #-->' );
            $stack->pop();
        }

        $gm->setVariable('key', '');
        $this->addBuffer($buffer);
    }

    /**
     * SearchObjectSettings::$SearchListJPからセレクトフォームを生成
     */
    function drawSelect(&$gm, $rec, $args) {
        if(count($args) < 2) {
            throw new Exception('CCの引数が足りません。');
        }
        $jpNames = $this->getJPNames();
        $names = $this->getNames();
        if(isset($args[2]) && strlen($args[2])) {
            $jpNames = $args[2].'/'.$jpNames;
            $names = '/'.$names;
        }
        $cc = new ccProc();
        $m = 'form';
        $buffer = $cc->{$m}($gm, $rec, array($m, 'option', $args[0], $args[1], $names, $jpNames));
        $this->addBuffer($buffer);
    }

    function drawNames(&$gm, $rec, $args) {
        $this->addBuffer($this->getNames());
    }

    function drawJPNames(&$gm, $rec, $args) {
        $this->addBuffer($this->getJPNames());
    }

    private function getNames() {
        return implode('/', array_keys(SearchObjectSettings::$SearchListJP));
    }
    private function getJPNames() {
        return implode('/', SearchObjectSettings::$SearchListJP);
    }

    private static $type = 'edittext';
}