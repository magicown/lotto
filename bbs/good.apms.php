<?php
include_once('./_common.php');

$is_apms = true;

$txt_good = '추천';
$txt_nogood = '비추천';

@include_once($board_skin_path.'/good.head.skin.php');

// 자바스크립트 사용가능할 때
if($_POST['js'] == "on") {
    $error = $success = $count = "";

    function print_result($error, $success, $count) {
		echo '{ "error": "' . $error . '", "success": "' . $success . '", "count": "' . $count . '" }';
		exit;
	}

    if (!$is_member) {
        $error = '회원만 가능합니다.';
        print_result($error, $success, $count);
    }

    if (!($bo_table && $wr_id)) {
        $error = '값이 제대로 넘어오지 않았습니다.';
        print_result($error, $success, $count);
    }

    $ss_name = 'ss_view_'.$bo_table.'_'.$wr_id;
    if (!get_session($ss_name)) {
        $error = '해당 게시물에서만 추천 또는 비추천 하실 수 있습니다.';
        print_result($error, $success, $count);
    }

    $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} ", FALSE);
    if (!$row['cnt']) {
        $error = '존재하는 게시판이 아닙니다.';
        print_result($error, $success, $count);
    }

    if ($good == 'good' || $good == 'nogood') {
        if($write['mb_id'] == $member['mb_id']) {
            $error = '자신의 글에는 추천 또는 비추천 하실 수 없습니다.';
	        print_result($error, $success, $count);
        }

        if (!$board['bo_use_good'] && $good == 'good') {
            $error = '이 게시판은 추천 기능을 사용하지 않습니다.';
	        print_result($error, $success, $count);
        }

        if (!$board['bo_use_nogood'] && $good == 'nogood') {
            $error = '이 게시판은 비추천 기능을 사용하지 않습니다.';
	        print_result($error, $success, $count);
        }

        $sql = " select bg_flag from {$g5['board_good_table']}
                    where bo_table = '{$bo_table}'
                    and wr_id = '{$wr_id}'
                    and mb_id = '{$member['mb_id']}'
                    and bg_flag in ('good', 'nogood') ";
        $row = sql_fetch($sql);
        if ($row['bg_flag']) {
            if ($row['bg_flag'] == 'good')
                $status = ($txt_good) ? $txt_good : '추천';
            else
                $status = ($txt_nogood) ? $txt_nogood : '비추천';

            $error = "이미 $status 하셨습니다.";
	        print_result($error, $success, $count);

		} else {

			@include_once($board_skin_path.'/good.skin.php');

			// 추천(찬성), 비추천(반대) 카운트 증가
            sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_{$good} = wr_{$good} + 1 where wr_id = '{$wr_id}' ");

			// 내역 생성
            sql_query(" insert {$g5['board_good_table']} set bo_table = '{$bo_table}', wr_id = '{$wr_id}', mb_id = '{$member['mb_id']}', bg_flag = '{$good}', bg_datetime = '".G5_TIME_YMDHIS."' ");

			// APMS : 내글반응
			apms_response('wr', $good, '', $bo_table, $wr_id, $write['wr_subject'], $write['mb_id'], $member['mb_id'], $member['mb_nick']);

			// 새글DB 업데이트
			apms_board_new('as_'.$good, $bo_table, $wr_id);

            $sql = " select wr_{$good} as count from {$g5['write_prefix']}{$bo_table} where wr_id = '$wr_id' ";
            $row = sql_fetch($sql);

            $count = $row['count'];

            if ($good == 'good')
                $status = ($txt_good) ? $txt_good : '추천';
			else
                $status = ($txt_nogood) ? $txt_nogood : '비추천';

            $success = $status." 하셨습니다.";

			@include_once($board_skin_path.'/good.tail.skin.php');

	        print_result($error, $success, $count);
        }
    }
} else {
    include_once(G5_PATH.'/head.sub.php');

    if (!$is_member) {
        $href = './login.php?'.$qstr.'&amp;url='.urlencode('./board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);

        alert('회원만 가능합니다.', $href);
    }

    if (!($bo_table && $wr_id))
        alert('값이 제대로 넘어오지 않았습니다.');

    $ss_name = 'ss_view_'.$bo_table.'_'.$wr_id;
    if (!get_session($ss_name))
        alert('해당 게시물에서만 추천 또는 비추천 하실 수 있습니다.');

    $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} ", FALSE);
    if (!$row['cnt'])
        alert('존재하는 게시판이 아닙니다.');

    if ($good == 'good' || $good == 'nogood') {
        if($write['mb_id'] == $member['mb_id'])
            alert('자신의 글에는 추천 또는 비추천 하실 수 없습니다.');

        if (!$board['bo_use_good'] && $good == 'good')
            alert('이 게시판은 추천 기능을 사용하지 않습니다.');

        if (!$board['bo_use_nogood'] && $good == 'nogood')
            alert('이 게시판은 비추천 기능을 사용하지 않습니다.');

        $sql = " select bg_flag from {$g5['board_good_table']}
                    where bo_table = '{$bo_table}'
                    and wr_id = '{$wr_id}'
                    and mb_id = '{$member['mb_id']}'
                    and bg_flag in ('good', 'nogood') ";
        $row = sql_fetch($sql);
        if ($row['bg_flag']) {
            if ($row['bg_flag'] == 'good')
                $status = ($txt_good) ? $txt_good : '추천';
			else
                $status = ($txt_nogood) ? $txt_nogood : '비추천';

            alert("이미 $status 하셨습니다.");

		} else {

			@include_once($board_skin_path.'/good.skin.php');

			// 추천(찬성), 비추천(반대) 카운트 증가
            sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_{$good} = wr_{$good} + 1 where wr_id = '{$wr_id}' ");

			// 내역 생성
            sql_query(" insert {$g5['board_good_table']} set bo_table = '{$bo_table}', wr_id = '{$wr_id}', mb_id = '{$member['mb_id']}', bg_flag = '{$good}', bg_datetime = '".G5_TIME_YMDHIS."' ");

			// 내글반응
			apms_response('wr', $good, '', $bo_table, $wr_id, $write['wr_subject'], $write['mb_id'], $member['mb_id'], $member['mb_nick']);

			// 새글DB 업데이트
			apms_board_new('as_'.$good, $bo_table, $wr_id);

            if ($good == 'good')
                $status = ($txt_good) ? $txt_good : '추천';
			else
                $status = ($txt_nogood) ? $txt_nogood : '비추천';

            $href = './board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;

			@include_once($board_skin_path.'/good.tail.skin.php');

            alert("$status 하셨습니다.", '', false);
        }
    }
}

?>