<?php
class edittextSystem extends System {
    /**
     * @param string $loginUserType
     * @param string $loginUserRank
     */
    public function otherProc( $loginUserType, $loginUserRank ) {
        if(!($loginUserType == 'admin' && isset($_POST['mode']) && strlen($_POST['mode']))) {
            return;
        }
        switch($_POST['mode']) {
            case 'save':
                if(!(isset($_GET['file']) && strlen($_GET['file']))) {
                    return;
                }
                $searchContainer = array();
                $otherList = array();
                $settings = SearchObjectSettings::$SearchList;
                foreach($_POST as $key => $val ) {
                    if(is_array($val)) {
                        $searchParameter = array();
                        foreach($val['name'] as $name) {
                            if(isset($settings[$name])) {
                                $searchParameter[] = new SearchParameter($name, $settings[$name][0], $settings[$name][1], $settings[$name][2]);
                            }
                        }
                        if(!count($searchParameter)) {
                            continue;
                        }
                        $searchContainer[] = new SearchContainer($searchParameter, $val['sprintText']);
                    } else if(in_array($key, array('rowNum', 'addText'))) {
                        $otherList[$key] = $val;
                    }
                }
                $svm = new SearchValueManager($_GET['file'], $_POST['defaultText'], $_POST['detailText'], $searchContainer);
                $svm->setOtherList($otherList);
                $svm->save();
                break;
            case 'delete':
                if(!(isset($_GET['file']) && strlen($_GET['file']))) {
                    return;
                }
                SearchValueManager::delete($_GET['file']);
                SystemUtil::innerLocation('other.php?key=edittext_list&type=edittext');
                break;
            case 'init':
                SearchObjectSettings::init();
                break;
        }
    }
}