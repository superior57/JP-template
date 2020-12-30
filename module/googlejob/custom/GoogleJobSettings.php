<?php
final class GoogleJobSettings {
    const EMPLOYMENT_ITEMS_TYPE = 'items_form';

    const EMPLOYMENT_TYPE_OTHER = 'OTHER';
    const EMPLOYMENT_TYPE = 'FULL_TIME/PART_TIME/CONTRACTOR/TEMPORARY/INTERN/VOLUNTEER/PER_DIEM/OTHER';
    const EMPLOYMENT_TYPE_JP = '正社員：FULL_TIME/アルバイト・パート：PART_TIME/契約社員：CONTRACTOR/派遣社員：TEMPORARY/インターン：INTERN/ボランティア：VOLUNTEER/日雇い：PER_DIEM/その他：OTHER';

    public static $DispUserTypes = array('nobody', 'nUser');
    public static $JobTypes = array('mid', 'fresh');
}
