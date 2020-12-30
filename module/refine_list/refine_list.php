<?php
class mod_refine_list extends command_base {

    /**
     * ヒットしたパラメータの優先順位
     * 
     * table ヒットしたパラメータの対になるテーブル
     * parameter リンクに付加するパラメータ配列
     * label_list 表示するRefineList.htmlの readheadラベル名 => front_flg の配列
     */
    public static $REFINE_PARAMETER_LIST = array(
        'category' => array(
            'table' => 'items_type',
            'label_list' => array(
                'work_place_adds' => true,
                'work_style' => true,
                'addition' => true
            )
        ),
        'work_place_adds' => array(
            'table' => 'adds',
            'parameter' => array(
                'foreign_flg' => 'FALSE',
                'foreign_flg_PAL' => array( 'match comp' )
            ),
            'label_list' => array(
                'category' => false,
                'work_style' => true,
                'addition' => true
            )
        ),
        'work_style' => array(
            'table' => 'items_form',
            'not_disp_type' => array('fresh'),
            'label_list' => array(
                'category' => false,
                'work_place_adds' => false,
                'addition' => true
            )
        ),
        'addition' => array(
            'table' => 'job_addition',
            'label_list' => array(
                'category' => false,
                'work_place_adds' => false,
                'work_style' => false
            )
        )
    );

    //REFINE_PARAMETER_LISTのパラメータに値が無かった場合のデフォルト値
    private static $REFINE_PARAMETER_DEFAULT = array(
        'parameter' => array(),
        'not_disp_type' => array(),
        'label_list' => array()
    );

    /**
     * ヒットした職種や都道府県のリンクリストを出力する
     * @param $args[0] 求人タイプ
     */
    public function drawRefineList(&$gm, $rec, $args)
    {
        global $loginUserType;
        global $loginUserRank;

        $jobType = isset($args[0]) && strlen($args[0]) ? $args[0] : (isset($_GET['type']) ? $_GET['type'] : 'mid');
        $design = Template::getTemplate( $loginUserType , $loginUserRank , 'refine_list', 'REFINE_LIST_DESIGN' );
        $buffer = '';
        $paramList = array();

        $urlFlg = isset($GLOBALS['STATIC_URL_FLG']) ? $GLOBALS['STATIC_URL_FLG'] : false;
        $gm->setVariable('JOB_TYPE', $jobType);
        $gm->setVariable('STATIC_URL_FLG', $urlFlg ? 'TRUE' : 'FALSE');

        foreach(self::$REFINE_PARAMETER_LIST as $name => $_data) {
            $data = array_merge(self::$REFINE_PARAMETER_DEFAULT, $_data);
            //not_disp_typeと求人タイプが同じだった場合スルー
            if(in_array($jobType, $data['not_disp_type'])) {
                continue;
            }
            $paramExistsFlg = false;

            //静的URLでリンクするかどうか
            if($urlFlg) {
                if($this->getParameterValue($_GET, $name, $data['table'], $paramValue, $jp)) {
                    if(!$this->existsOtherParameter($_GET, $name)) {
                        $gm->setVariable('JP', $jp);
                        $gm->setVariable('ADD_PARAMETER', $paramValue);
                        $paramExistsFlg = true;
                    }
                }
            } else if($this->getParameterList($_GET, $name, $data['table'], $paramList, $jp)) {
                if(!$this->existsOtherParameter($_GET, $name)) {
                    $paramList = count($data['parameter']) ? $paramList + $data['parameter'] : $paramList;

                    $gm->setVariable('JP', $jp);
                    $gm->setVariable('ADD_PARAMETER', SystemUtil::getUrlParm($paramList));
                    $paramExistsFlg = true;
                }
            }

            //パラメータが存在すれば描画
            if($paramExistsFlg && count($data['label_list'])) {
                foreach($data['label_list'] as $label => $front_flg) {
                    //not_disp_typeと求人タイプが同じだった場合スルー
                    if(isset(self::$REFINE_PARAMETER_LIST[$label]['not_disp_type']) && in_array($jobType, self::$REFINE_PARAMETER_LIST[$label]['not_disp_type'])) {
                        continue;
                    }
                    $gm->setVariable('FRONT_FLG', $front_flg ? 'TRUE' : 'FALSE');
                    $buffer .= $gm->getString($design, $rec, $label);
                }
                break;
            }
        }

        $this->addBuffer($buffer);
    }

    /**
     * 対象パラメータのみを取り出し再構築
     * @param array $param パラメータ配列
     * @param string $paramName 処理対象のパラメータ名
     * @param string $tableType 処理対象のパラメータの対になるテーブル名
     * @param string $addPal 処理対象のパラメータに付加するPAL 処理対象が配列でない場合は強制的にmatch+compになる
     * @param array &$paramList 処理したパラメータが入る
     * @param string &$jp 処理対象のパラメータのnameカラムの値が入る
     * @return boolean 対象パラメータが存在して処理が行われた場合true
     */
    private function getParameterList(array $param, $paramName, $tableType, array &$paramList = array(), &$jp = '')
    {
        if($this->getParameterValue($param, $paramName, $tableType, $paramValue, $jp)) {
            $paramList[$paramName] = $paramValue;
            $paramList[$paramName.'_PAL'][] = 'match comp';
            return true;
        }
        return false;
    }

    /**
     * 対象パラメータのみを取り出し再構築
     * @param array $param パラメータ配列
     * @param string $paramName 処理対象のパラメータ名
     * @param string $tableType 処理対象のパラメータの対になるテーブル名
     * @param string $addPal 処理対象のパラメータに付加するPAL 処理対象が配列でない場合は強制的にmatch+compになる
     * @param string &$paramValue 処理したパラメータが入る
     * @param string &$jp 処理対象のパラメータのnameカラムの値が入る
     * @return boolean 対象パラメータが存在して処理が行われた場合true
     */
    private function getParameterValue(array $param, $paramName, $tableType, &$paramValue = '', &$jp = '')
    {
        if(isset($param[$paramName])) {
            $val = $param[$paramName];
            if (is_array($val)) {
                foreach ($val as $v) {
                    if (strlen($v)) {
                        $paramValue = $v;
                        $jp = SystemUtil::getTableData($tableType, $v, 'name');
                        return true;
                    }
                }
            } else if (strlen($val)) {
                $paramValue = $val;
                $jp = SystemUtil::getTableData($tableType, $val, 'name');
                if(!is_null($jp)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * $paramにlabel_listのキー名と一致するパラメータが存在すればtrue
     */
    private function existsOtherParameter(array $param, $paramName) 
    {
        if(!isset(self::$REFINE_PARAMETER_LIST[$paramName])) {
            return false;
        }

        $data = array_merge(self::$REFINE_PARAMETER_DEFAULT, self::$REFINE_PARAMETER_LIST[$paramName]);
        if(!count($data['label_list'])) {
            return false;
        }
        $refineLabelList = $data['label_list'];
        if(isset($refineLabelList[$paramName])) {
            unset($refineLabelList[$paramName]);
        }
        $nameList = array_keys($refineLabelList);
        foreach($nameList as $name) {
            if(isset($param[$name])) {
                if (is_array($param[$name])) {
                    foreach ($param[$name] as $v) {
                        if (strlen($v)) {
                            return true;
                        }
                    }
                } else if (strlen($param[$name])) {
                    return true;
                }
            }
        }
        return false;
    }
}