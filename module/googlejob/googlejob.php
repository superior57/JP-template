<?php
class mod_googlejob extends command_base{
    public function drawMarkUP( &$gm, $rec, $args ) {
        global $loginUserType;
        global $loginUserRank;
        // GoogleJobSettingsに設定されたユーザーでなければスルー
        if(!in_array($loginUserType, GoogleJobSettings::$DispUserTypes)) {
            return;
        }
        // 表示設定が非表示の場合スルー
        if(!Conf::getData(self::$type, 'disp_flg')) {
            return;
        }
        // typeがGoogleJobSettingsに設定されたtypeで かつ 求人レコードがセットされていれば表示する
        if(
            isset($_GET['type']) &&
            in_array($_GET['type'], GoogleJobSettings::$JobTypes) &&
            GoogleJobLogic::existsJobRecord()
        ) {
            $uGM = GMList::getGM($_GET['type']);
            $filePath = Template::getTemplate( $loginUserType, $loginUserRank, self::$type, 'TEMPLATE_DESIGN' );
            $this->addBuffer($uGM->getString( $filePath, GoogleJobLogic::getJobRecord(), $_GET['type'] ));
        }
    }

    /**
     * レコードの改行削除
     */
    public function delN(&$gm, $rec, $args)
    {
        global $SYSTEM_CHARACODE;
        if(!GoogleJobLogic::existsJobRecord()) {
            return;
        }
        List($col) = $args;
        $uGM = GMList::getGM($_GET['type']);
        $uDB = $uGM->getDB();
        $data = $uDB->getData(GoogleJobLogic::getJobRecord(), $col);
        $data = preg_replace('/(?:\n|\r|\r\n)/', '', $data );
        $this->addBuffer($data);
    }

    public function drawEmploymentType(&$gm, $rec, $args) {
        List($id) = $args;
        if(!GoogleJobLogic::existsEmployment()){
            GoogleJobLogic::setEmploymentByJson(Conf::getData(self::$type, 'employment_json'));
        }
        $this->addBuffer(GoogleJobLogic::getEmployment($id));
    }

    /**
     * クラス定数の値を文字列で参照
     */
    public function drawConst(&$gm, $rec, $args)
    {
        if (count((array) $args) < 2) {
            throw new InvalidCCArgumentException('CC構文エラー:引数が足りません');
        }

        $classConst = $args[0] . '::' . $args[1];

        if (defined($classConst)) {
            $res = constant($classConst);
            if (is_bool($res)) {
                $res = $res ? 'TRUE' : 'FALSE';
            }
            $this->addBuffer($res);
        } else {
            throw new InvalidCCArgumentException('CC構文エラー:指定したクラス定数は存在しません');
        }
    }

    /**
     * クラス変数の値を文字列で参照
     */
    public function drawStatic(&$gm, $rec, $args)
    {
        if (count((array) $args) < 2) {
            throw new InvalidCCArgumentException('CC構文エラー:引数が足りません');
        }

        $className = $args[0];
        $staticName = $args[1];
        if (class_exists($className)) {
            $c = new ReflectionClass($className);
            if ($c->hasProperty($staticName)) {
                $res = $c->getStaticPropertyValue($staticName);
                if (is_bool($res)) {
                    $res = $res ? 'TRUE' : 'FALSE';
                }
                $this->addBuffer($res);
            } else {
                throw new InvalidCCArgumentException('CC構文エラー:指定したクラス変数は存在しません');
            }
        } else {
            throw new InvalidCCArgumentException('CC構文エラー:指定したクラスは存在しません');
        }
    }

    /**
     * グローバル配列変数の値を文字列で参照
     */
    public function drawArray(&$gm, $rec, $args)
    {
        if (count((array) $args) < 2) {
            throw new InvalidCCArgumentException('CC構文エラー:引数が足りません');
        }
        if (isset($args[2]) && strlen($args[2]) && isset($GLOBALS[$args[0]])) {
            $this->addBuffer(implode($args[2], $GLOBALS[$args[0]]));
        } else if (isset($GLOBALS[$args[0]][$args[1]])) {
            $this->addBuffer($GLOBALS[$args[0]][$args[1]]);
        } else {
            throw new InvalidCCArgumentException('CC構文エラー:指定したグローバル変数は存在しません');
        }
    }

    /**
     * リフレクションによってメソッドを呼び出すCFはCallFunctionの略
     * 引数には基本的にString型しか指定できないので注意してください
     */
    function CF(&$gm, $rec, $args)
    {
        if (!isset($args[0])) {
            throw new InvalidCCArgumentException('CC構文エラー:引数が足りません');
        }
        $m = array_shift($args);
        $reflectionFunction = new ReflectionFunction($m);
        $res = '';

        $ret = $reflectionFunction->invokeArgs($args);
        if (is_bool($ret)) {
            $res = $ret ? 'TRUE' : 'FALSE';
        } else {
            $res = $ret;
        }
        $this->addBuffer($res);
    }

    private static $type = 'googlejob';
}
