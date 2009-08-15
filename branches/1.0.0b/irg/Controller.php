<?php
chdir('../../');
require("include/auth.php");
require("IRG.php");
$api = IRG::getInstance();

# action
$a = $_GET['a'];

switch ($a) {
    case 'graph':
        header("Content-type: application/json");
        if(array_key_exists('id', $_GET)){
            $graph = $api->getCactiGraph($_GET['id']);
        } else{
            $graph = $api->getCactiGraph();
        }

        echo json_encode($graph);
        break;
    case 'host':
        header("Content-type: application/json");
        if(array_key_exists('id', $_GET)){
            $hosts = $api->getCactiHost($_GET['id']);
        } else{
            $hosts = $api->getCactiHost();
        }

        echo json_encode($hosts);
        break;
    case 'report':
        header("Content-type: application/json");
        $reportPerPage = 30;

        if(array_key_exists('id', $_GET)){
            $report = $api->getReportByID($_GET['id']);
        } else{
            $lastReportLimit = $_COOKIE['last_report_limit'];
            $limit_start = $lastReportLimit;
            $limit_end = $lastReportLimit + $reportPerPage;
            $lastReportLimit = $limit_end;
            $report = $api->getReport($limit_start, $limit_end);
        }

        echo json_encode($report);
        break;
    case 'rra':
        header("Content-type: application/json");
        if(array_key_exists('id', $_GET)){
            $rras = $api->getCactiRRAType($_GET['id']);
        } else{
            $rras = $api->getCactiRRAType();
        }

        echo json_encode($rras);
        break;
    case 'timestamp':
        header("Content-type: application/json");

        $beginTimeStamp = $api->getTimeStamp($_GET['beginDateTime']);
        $endTimeStamp = $api->getTimeStamp($_GET['endDateTime']);

        echo "{'beginTimeStamp':$beginTimeStamp, 'endTimeStamp':$endTimeStamp}";
        break;
    default:
        echo "Error: action not found!";
        break;
}
?>
