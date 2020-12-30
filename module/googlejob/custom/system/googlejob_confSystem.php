<?php
class googlejob_confSystem extends System {

    public function editProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false)
    {
        $db = $gm[$_GET['type']]->getDB();
        $db->setData($rec, 'edit', time());

        if(isset($_POST['employment']) && is_array($_POST['employment']) && count($_POST['employment'])) {
            $json = json_encode($_POST['employment']);
            $db->setData($rec, 'employment_json', $json);
            GoogleJobLogic::setEmployment($_POST['employment']);
        }

        parent::editProc($gm, $rec, $loginUserType, $loginUserRank, $check);
    }

    public function drawEditForm(&$gm, &$rec, $loginUserType, $loginUserRank)
    {
        $db = $gm[$_GET['type']]->getDB();

        GoogleJobLogic::setEmploymentByJson($db->getData($rec, 'employment_json'));
        parent::drawEditForm($gm, $rec, $loginUserType, $loginUserRank);
    }
}
