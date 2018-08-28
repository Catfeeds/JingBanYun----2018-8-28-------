<?php
error_reporting(E_ALL ^ E_NOTICE);
define('GROUP_VIP',3);

session_start();
switch(getServiceName())
  {
      case 'eTextbook': switch(getSubServiceName()){
                             case "config":$url = $_GET['baseUrl']."/Pages/page{$_GET['p']}/index.xml";
                                            getAndAuthOutputFile($url);
                                            break;
                             case "subTitle":
                                            $url = $_GET['baseUrl']."/Pages/page{$_GET['p']}/kalaok.txt";
                                            getAndAuthOutputFile($url);
                                            break;
                             default:break;
                        }
                        break;
      default:break;
  }


function isAuth()
{
    if (($_SESSION['teacher_vip'] && $_SESSION['teacher_vip']['vip_data'][0]['auth_id'] == GROUP_VIP) ||
        ($_SESSION['student_vip'] && $_SESSION['student_vip']['vip_data'][0]['auth_id'] == GROUP_VIP) ||
        ($_SESSION['parent_vip'] && $_SESSION['parent_vip']['vip_data'][0]['auth_id'] == GROUP_VIP)
    ) {
        return true;
    }
    return false;
}

function getServiceName()
{
    return $_GET['service'];
}

function getSubServiceName()
{
    return $_GET['subService'];
}

function getAndAuthOutputFile($url)
{
    $result = file_get_contents($url);
    $p = xml_parser_create();
    xml_parse_into_struct($p, $result, $vals, $index);
    xml_parser_free($p);
    $isCharge = $vals[0]['attributes']['ISCHARGE'];
    if(!isAuth() && $isCharge==1) {
        echo json_encode(['status'=>500,'message'=>'auth failed']);
        exit;
    }
    if(strpos(strtolower($url),'.xml') !== false)
     header('Content-Type: text/xml');
    echo $result;
}