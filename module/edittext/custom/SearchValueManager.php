<?php
/**
 * toStringとgetValueを実装するインタフェース
 */
interface Searchable {
    /**
     * @return string
     */
    public function toString();

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue($name);
}

/**
 * toArray toObjectを継承させるための全てのSearch--系の親クラス
 */
class SearchObject implements Searchable {
    public function toString() {
        return 'CLASSNAME:'.get_class($this);
    }

    /**
     * 自身のプロパティかどうかチェックしてプロパティの値を返す
     * @param string $name プロパティ名
     * @return mixed
     */
    public function getValue($name) {
        if(!property_exists($this, $name)) {
            throw new EditTextException(get_class($this).'クラスには'.$name.'というプロパティが存在しません');
        }
        return $this->{$name};
    }
}

/**
 * SearchContainerを管理するクラス
 * 最終的な文字出力を行う
 * これからファイルに変数内容を出力しテンプレート上で編集できるようにする予定
 */
class SearchValueManager extends SearchObject {
    private $type = null;
    protected $detailText = '';
    protected $defaultText = '';
    protected $scList = array();

    protected $otherList = array();

    private static $saveDirPath = 'file/edittext/'; 
    private static $saveExt = '.dat';
    private static $loadCache = array();

    /**
     * @param string $type タイプ名
     * @param string $defaultText 条件に一致しなかった場合出力するデフォルトの文字
     * @param string $detailText 説明文
     * @param array $scList SearchContainerの配列
     */
    function __construct($type, $defaultText, $detailText, array $scList = array()) {
        $this->type = $type;
        $this->defaultText = $defaultText;
        $this->detailText = $detailText;
        $this->scList = $scList;

        self::mkdir();
    }

    /**
     * 
     */
    function getString($params, $addstr = '') {
        $resObj = null;
        foreach($this->scList as $scObj) {
            if($scObj->existsParameter($params)) {
                $resObj = $scObj;
                break;
            }
        }
        if(is_null($resObj)) {
            return $this->defaultText;
        }
        return $resObj->getString($params, $addstr);
    }

    /**
     * 変数の内容を指定のフォルダに保存
     */
    public function save() {
        $saveList['scList'] = $this->scList;
        $saveList['defaultText'] = $this->defaultText;
        $saveList['detailText'] = $this->detailText;
        $saveList['otherList'] = $this->getOtherList();

        self::$loadCache[$this->type] = $this;
        $data = serialize($saveList);
        file_put_contents(self::$saveDirPath.$this->type.self::$saveExt, $data);
    }

    /**
     * otherListにプリミティブ型の値をセットする
     */
    public function setOther($key, $val) {
        if(is_bool($val) || is_double($val) || is_float($val) || is_string($val) || is_int($val)) {
            $this->otherList[$key] = $val;
        }
    }

    /**
     * otherListに配列でまとめてセットする
     */
    public function setOtherList(array $list = array()) {
        foreach($list as $key => $val) {
            $this->setOther($key, $val);
        }
    }

    /**
     * otherListから値を取り出す。値が無ければEditTextException例外発生
     */
    public function getOther($key) {
        if(!isset($this->otherList[$key])) {
            throw new EditTextException('otherParameterに、そのキーは存在しません');
        }
        return $this->otherList[$key];   
    }

    /**
     * otherListを取得
     */
    public function getOtherList() {
        return $this->otherList;
    }

    /**
     * 定義したフォルダパスの指定タイプのファイルを見つけロードする。
     * @param string $type saveしたタイプ
     * @param array 検索対象となる配列、主に$_GETになる。
     * @return mixed ファイルが存在した場合SearchValueManagerオブジェクトを返すが、ファイルが存在しなかった場合nullを返す
     */
    public static function load($type) {
        if(isset(self::$loadCache[$type])) {
            return self::$loadCache[$type];
        }
        if(self::existsTypeFile($type, $path)) {
            $data = file_get_contents($path);
        } else {
            $svm = new SearchValueManager($type, '', '');
            $svm->setOther('rowNum', 0);
            $svm->setOther('addText', '');
            return $svm;
        }
        $loadObj = unserialize($data);
        //return new static($type, $params, $loadObj['defaultText'], $loadObj['list']);
        $svm = new SearchValueManager($type, $loadObj['defaultText'], $loadObj['detailText'], $loadObj['scList']);
        $svm->setOtherList($loadObj['otherList']);

        self::$loadCache[$type] = $svm;
        return self::$loadCache[$type];
    }

    /**
     * $saveDirPathのパスが存在しなかった場合ディレクトリ作成
     */
    public static function mkdir() {
        if( !is_dir( self::$saveDirPath ) ) {
            mkdir( self::$saveDirPath, 0777 );
            chmod( self::$saveDirPath, 0777 );
        }
    }

    /**
     * 指定タイプのファイルが存在するかどうか
     */
    public static function existsTypeFile($type, &$path = '') {
        $path = self::getFilePath($type);
        return file_exists($path);
    }

    /**
     * 指定タイプのファイルを削除
     */
    public static function delete($type) {
        if(self::existsTypeFile($type, $path)) {
            return unlink($path);
        }
        return false;
    }
    
    /**
     * $saveDirPathのパス上にファイルが存在している場合
     * タイプ名一覧を配列で返す
     */
    public static function getTypeList() {
        if( is_dir( self::$saveDirPath ) ) {
            $result = array();
            $list = scandir(self::$saveDirPath);
            foreach($list as $file) {
                if(strpos($file, self::$saveExt) !== false) {
                    $result[] = str_replace(self::$saveExt, '', $file);
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * タイプからパスを返す
     */
    public static function getFilePath($type) {
        return self::$saveDirPath.$type.self::$saveExt;
    }
}

/**
 * パラメータ配列から何をどう取り出すか定義するクラス
 */
class SearchParameter extends SearchObject {
    public $name = ''; //パラメータの名前
    public $tableName = ''; //テーブル名
    public $keyName = ''; //パラメータの値とテーブルの一致させるカラム名
    public $colName = ''; //テーブルから取り出すカラム

    function __construct($name, $tableName, $keyName = 'id', $colName = 'name') {
        $this->name = $name;
        $this->tableName = $tableName;
        $this->keyName = $keyName;
        $this->colName = $colName;
    }

    /**
     * パラメータ配列から条件に合ったパラメータを取り出す
     */
    public function getNameValue($params) {
        if(isset($params[$this->name])) {
            if(is_array($params[$this->name])) {
                if(count($params[$this->name])) {
                    $list = array_filter($params[$this->name], 'strlen');
					return count($list) > 0 ? $list : false;
				}
			} else {
				return (strlen($params[$this->name]) > 0 ? $params[$this->name] : false);
			}
        }
        return false;
    }

    /**
     * パラメータの値から名前をテーブルから取り出す
     * @param array 照合するパラメータ配列
     * @return mixed 複数ある場合arary 単一の場合string 何もなかった場合false
     */
    public function getKeyValue($params) {
        $keyVal = $this->getNameValue($params);
        if($keyVal !== false) {
            if($this->tableName == '->' || !strlen($this->tableName)) {
                $method = $this->keyName;
                $sou = new SearchObjectUtil();
                if(method_exists($sou, $this->keyName)) {
                    return $sou->{$method}($keyVal);
                } else {
                    return false;
                }
            }
            $db = GMList::getDB($this->tableName);
            $table = $db->getTable();
            if(is_array($keyVal)) {
                $table = $db->searchTable($table, $this->keyName, 'in', $keyVal);
                return $db->getDataList($table, $this->colName);
            }
            $table = $db->searchTable($table, $this->keyName, '=', $keyVal);
            $rec = $db->getFirstRecord($table);
            $db->cashReset();
            return $db->getData($rec, $this->colName);
        }
        return false;
    }

    /**
     * SearchObjectSettingsのパラメータの値でオブジェクトを生成
     */
    public static function create(array $list = array()) {
        $result = array();
        foreach($list as $key) {
            if(isset(SearchObjectSettings::$SearchList[$key])) {
                $arr = SearchObjectSettings::$SearchList[$key];
                $result[] = new SearchParameter($key, $arr[0], $arr[1], $arr[2]);
            }
        }
        return $result;
    }
}

/**
 * SearchParameterを管理するクラス
 */
class SearchContainer extends SearchObject {
    protected $sprintText = ''; //vsprintで文字変換するフォーマット
    protected $paramObjectList = array(); //SearchParameterのリスト

    function __construct($paramObjectList, $sprintText = '') {
        if(!count($paramObjectList)) {
            throw new Exception('SearchParameterのリストを一つ以上指定してください。');
        }
        $this->paramObjectList = $paramObjectList;
        $this->sprintText = $sprintText;
    }

    /**
     * @param array 照合するパラメータ配列
     * @return bool SearchParameterリストがすべて含まれていた場合true そうじゃない場合false
     */
    public function existsParameter($params) {
        foreach($this->paramObjectList as $searchObj) {
            if($searchObj->getNameValue($params) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * パラメータ名keyとテーブルから取り出した値valの連想配列を返す
     * @param array 照合するパラメータ配列
     * @return array
     */
    public function getKeyValueList($params) {
        foreach($this->paramObjectList as $searchObj) {
            $val = $searchObj->getKeyValue($params);
            if(is_array($val)) {
                $result[$searchObj->name] = array_shift($val);
            } else {
                $result[$searchObj->name] = $val;
            }
        }
        return $result;
    }

    /**
     * $sprintTextとパラメータから取り出した値から変換した文字列を返す
     * @param array 照合するパラメータ配列
     * @return string
     */
    public function getString($params, $addstr = '') {
        if($this->existsParameter($params)) {
            $list = $this->getKeyValueList($params);
            $list = array($addstr) + $list;
            $result = vsprintf($this->sprintText, $list);
        }
        return $result;
    }
}
