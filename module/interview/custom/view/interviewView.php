<?PHP

class interviewView extends command_base
{

    /**
     * 管理者の承認が必要かどうかチェックする
     *
     * @param mode regist/edit
     */
    function drawAdminActivateCheck(&$gm, $rec, $args)
    {
        $mode = $args[0];
        $this->addBuffer(Conf::checkData('interview', 'ad_check', $mode));
    }
}

?>