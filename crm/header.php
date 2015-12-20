<?php
    define('DEFAULT_CONTACT_NUM', 10);
    define('DEFAULT_MESSAGE_NUM', 20);
    define('DEFAULT_FOLLOW_NUM', 10);
    define('IMG_PATH_BASE', '../crm/img/');
    define('PHOTO_WEITH', 600);

    require_once 'lib/FCurl.php';
    $FCurl = new FCurl();

    if (!isset($_COOKIE['accessToken'])) {
        FCurl::header('login.php');
        exit();
    }else {
        $param['accessToken'] = $_COOKIE['accessToken'];
        $data = FCurl::get('check/checkAccessToken', $param);
        $GLOBALS['userId'] = $data;
        // if ($data['code'])  FCurl::header('login.php'); //header('location:http://'.$_SERVER['HTTP_HOST'].'/test/crm/login.php');
    }

    function attranslate($data) {
        return '<a href="user.php?userId='.$data['userId'].'">'.$data['name'].'</a>';
    }

    function eventTranslate($eventContent) {
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $eventContent, $result);
        foreach ($result[0] as $eventIndex) {
            $event = str_replace('#', '', $eventIndex);
            $eventContent = str_replace($eventIndex, '<a href="contact_hash.php?tagList=^'.$event.'">'.'#'.$event.'</a>', $eventContent);
        }
        $eventContent = eventHadAtTranslate($eventContent);
        return $eventContent;
    }

    function eventHadAtTranslate($eventContent) {
        preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $eventContent, $result);
        foreach ($result[0] as $noticeUserName) {
            $name = str_replace('@', '', $noticeUserName);
            // $userId = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/vstone/app/?r=user/getUserIdByNickname&name='.$name);
            // $userId = json_decode($userId, true);
            // $userId = $userId['data'];
            $userId = FCurl::get('user/getUserIdByNickname', array('name' => $name));
            if ($userId) {
                $eventContent = str_replace($noticeUserName, '<a href="user.php?userId='.$userId.'">'.'@'.$name.'</a>', $eventContent);
            }else {
                $eventContent = str_replace($noticeUserName, '', $eventContent);
            }

        }
        return $eventContent;
    }

    function tagTemplate($class, $name) {

    }

    function paging($request, $requestParam, $requestResultNum, $callbackFunctionName) {
        $requestParam['page'] = isset($requestParam['page']) ? $requestParam['page'] : 1;
        $currentPage = $requestParam['page'];
        $currentPageNum = getPagingNumByRequestName($request);

        $returnHtml = '<div class="center-align">';
        $returnHtml .= '<ul class="pagination">';
        if ($requestResultNum) {
            if($currentPage == 1) $returnHtml .= '<li class="disabled"><a href="#!"><i class="mdi-navigation-chevron-left"></i></a></li>';
            else {
                $requestParam['page']--;
                $returnHtml .= '<li class="waves-effect"><a href="'.$callbackFunctionName.'?'.http_build_query($requestParam).'"><i class="mdi-navigation-chevron-left"></i></a></li>';
                $requestParam['page']++;
            }

            $requestParam['page']++;
            if ($callbackFunctionName == 'contact_hash.php') {
                $tagListTmp = $_REQUEST['tagList'];
                $tagListTmp = explode('^', $tagListTmp);
                array_shift($tagListTmp);
                $tagListTmp = implode('&',$tagListTmp);

                $nextPageDaraNum = FCurl::get($request, $requestParam + array('accessToken' => $_COOKIE['accessToken'], 'num' => getPagingNumByRequestName($request), 'name' => $tagListTmp));
            }elseif ($callbackFunctionName == 'contact_info.php' || $callbackFunctionName == 'user.php') {
               $nextPageDaraNum = FCurl::get($request, $requestParam + array('accessToken' => $_COOKIE['accessToken'], 'num' => getPagingNumByRequestName($request)));
               $nextPageDaraNum = $nextPageDaraNum['returnData'];
            }
            else {
                $nextPageDaraNum = FCurl::get($request, $requestParam + array('accessToken' => $_COOKIE['accessToken'], 'num' => getPagingNumByRequestName($request)));
            }

            if(count($nextPageDaraNum)) $returnHtml .= '<li class="waves-effect"><a href="'.$callbackFunctionName.'?'.http_build_query($requestParam).'"><i class="mdi-navigation-chevron-right"></i></a></li>';
            else $returnHtml .= '<li class="disabled"><a href="#!"><i class="mdi-navigation-chevron-right"></i></a></li>';
        }
        $returnHtml .= '</ul><br><br>';
        $returnHtml .= '</div>';

        return $returnHtml;
    }

    function getPagingNumByRequestName($requestName) {
        $pageNum = DEFAULT_MESSAGE_NUM;
        switch ($requestName) {
            case 'user/getEventListByGroupId':
            case 'user/getEventList':
            case 'user/getUserEventList':
            case 'user/getNoticeList':
            case 'user/getLikeEventUserList':
			case 'group/getEventList':
			case 'group/seachByName':
                $pageNum = DEFAULT_MESSAGE_NUM;
            break;

            case 'user/getUserOwnerEventList':
                $pageNum = DEFAULT_FOLLOW_NUM;
            break;

            case 'user/seachUserList':
            case 'user/getList':
            case 'user/seachUserListByRoomNum':
                $pageNum = DEFAULT_CONTACT_NUM;
            break;

            default:
                $pageNum = DEFAULT_MESSAGE_NUM;
            break;
        }

        return $pageNum;
    }

    function getUserPhoto($url, $width = 55) {
        if (!$url) return '<i class="medium mdi-social-person-outline blue lighten-4 white-text circle"></i>';

        $url = 'img/'.$url;
        return '<img class="circle" src="'.$url.'" width="'.$width.'" />';
    }

    function getEventPhoto($url, $width = 100) {
        if (!$url) return '';

        $url = 'img/'.$url;
        $urlTmp = explode('.', $url);
        $fileType = array_pop($urlTmp);
        $urlSmall = $urlTmp[0].'_s.'.$fileType;
        //return '<a href="'.$url.'"><img src="'.$urlSmall.'" width="'.$width.'" /></a>';
        return '<img class="materialboxed" width="'.$width.'" src="'.$url.'">';
    }
	
	function showMobileStyle($phoneNum)
	{
		return substr_replace($phoneNum, '* * * *', 3, 4);
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="msapplication-tap-highlight" content="no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<title>Nash.CRM</title>

<link rel="apple-touch-icon-precomposed" href="images/favicon/apple-touch-icon-152x152.png">
<meta name="msapplication-TileColor" content="#FFFFFF">
<meta name="msapplication-TileImage" content="images/favicon/mstile-144x144.png">
<link rel="icon" href="images/favicon/favicon-32x32.png" sizes="32x32">
<!--  Android 5 Chrome Color-->
<meta name="theme-color" content="#EE6E73">

<link href="statics/css/materialize.min.css" rel="stylesheet">
<link rel="stylesheet" href="statics/css/crm.css" />
<script src="statics/js/func.js"></script>
</head>