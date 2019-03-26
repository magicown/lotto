<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 2019-03-26
 * Time: 오전 10:40
 */

ini_set("display_errors",1);
ini_set('memory_limit','-1');
include "./_common.php";

/*$temp = range(1,8145059);
shuffle($temp);
$data = array_slice($temp,0,20);
print_r($data);*/

//범위가 크면 랜덤 함수가 유용하지요. (어디까지나 하나의 예일 뿐)

$sql = "SELECT COUNT(*) AS cnt FROM lotto_num WHERE use_yn = 'N' AND mb_id IS NULL";
$row = sql_fetch($sql);
$total_cnt = $row['cnt'];

$cnt = 0;
$chk = $data = array();
while ( $cnt<20 )
{
    $rand = mt_rand(1,$total_cnt);
    if ( !isset($chk[$rand]) ) {
        $chk[$rand] = 1;
        $data[] = $rand;
        $cnt++;
    }
}

$lotto = implode(",",$data);

$que = "UPDATE lotto_num SET use_yn = 'Y', mb_id = 'test' WHERE idx IN ({$lotto})";
//echo $que."<br>";
sql_query($que);


$que = "UPDATE lotto_num SET use_yn = 'N', mb_id = NULL WHERE 1";
//sql_query($que);


