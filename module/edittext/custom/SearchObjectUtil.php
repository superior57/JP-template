<?php
/**
 * もしもSearchParameterのtableNameが空でkeyNameがあればkeyNameをメソッド名としてこのクラスメソッドを呼び出す
 */
class SearchObjectUtil {
    public function getFreeWord($keyVal) {
        return str_replace('　', ' ', $keyVal);
    }
}
