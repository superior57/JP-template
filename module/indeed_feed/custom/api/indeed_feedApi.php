<?php

class mod_indeed_feedApi
{
    public function updateIndeedFeed()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        // **************************************************************************************

        if ($loginUserType != "admin") {
            return;
        }

        indeed_feedLogic::updateIndeedFeed();
    }
}
