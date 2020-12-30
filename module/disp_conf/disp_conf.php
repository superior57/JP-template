<?php

class mod_disp_conf extends command_base
{
    private static $type = 'disp';

    /**
     * disp_confのデータを確認して論理値であればTRUEかFALSEを表示
     * (!--# mod disp_conf checkConf _アンダーバー以降のカラム名 #--)
     * <!--# ifbegin val= (!--# mod disp_conf checkConf free #--) TRUE #-->のように使う
     */
    function checkConf(&$gm, $rec, $args) {
        global $sp_mode;
        global $sp_flag;

        $col = $args[0];
        $buffer = '';
        $prefix = $sp_flag && $sp_mode ? 'sp_' : 'pc_';

        $res = Conf::getData(self::$type, $prefix.$col);

        if(is_bool($res)) {
            $buffer = $res ? 'TRUE' : 'FALSE';
        } else {
            $buffer = $res;
        }
        $this->addBuffer($buffer);
    }
}