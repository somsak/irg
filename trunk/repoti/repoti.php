<?php
chdir('../../');
require_once('include/auth.php');

if(!isset($_GET['c'])){
	header('Location: ./views/make_report.php');
}else {
	$c = $_GET['c'];
}

if(!isset($_GET['a'])){
	$a = null;
}else {
	$a = $_GET['a'];
}

switch ($c) {
	case 'hosts':
		require_once('plugins/repoti/controllers/hosts.php');
		header('Content-type: application/json');
		$h = new HostController();
		switch($a) {
			case 'get':
				echo json_encode($h->getHosts());
				break;

			case 'getById':
				echo json_encode($h->getHostById($_GET['hostId']));
				break;

			default:
				actionNotFound();
				break;
		}
		break;

	case 'graphs':
		require_once('plugins/repoti/controllers/graphs.php');
		header('Content-type: application/json');
		$g = new GraphController();
		switch($a) {
			case 'get':
				echo json_encode($g->getGraphs());
				break;

			case 'getByHostId':
				$hostId = $_GET['hostId'];
				echo json_encode($g->getGraphByHostId($hostId));
				break;

			case 'getById':
				$graphId = $_GET['graphId'];
				echo json_encode($g->getGraphById($graphId));
				break;

			case 'getstat':
				$graphId = $_GET['graphId'];
				$rraTypeId = $_GET['rraTypeId'];
				$timespan = $_GET['timespan'];
				$graphStart = $_GET['graphStart'];
				$graphEnd = $_GET['graphEnd'];
				$beginPrime = $_GET['beginPrime'];
				$endPrime = $_GET['endPrime'];

				echo json_encode($g->getGraphStat($graphId, $rraTypeId, $timespan, $graphStart, $graphEnd, $beginPrime, $endPrime));
				break;

			case 'getstats':
				if($_GET['graphIds'] != "") {
					$graphIds = explode(",", $_GET['graphIds']);
					$rraTypeId = $_GET['rraTypeId'];
					$timespan = $_GET['timespan'];
					$graphStart = $_GET['graphStart'];
					$graphEnd = $_GET['graphEnd'];
					$beginPrime = $_GET['beginPrime'];
					$endPrime = $_GET['endPrime'];
						
					echo json_encode($g->getGraphStats($graphIds, $rraTypeId, $timespan, $graphStart, $graphEnd, $beginPrime, $endPrime));
				}
				break;

			case 'getTemplate':
				echo json_encode($g->getTemplate($_GET['templateId']));
				break;

			default:
				actionNotFound();
				break;
		}
		break;

	case 'rras':
		require_once('plugins/repoti/controllers/rras.php');
		header('Content-type: application/json');
		$r = new RRAGraphController();

		switch($a) {
			case 'get':
				echo json_encode($r->getRRAs());
				break;

			case 'getById':
				echo json_encode($r->getRRAById($_GET['id']));
				break;

			default:
				actionNotFound();
				break;
		}
		break;

	case 'utils':
		require_once('plugins/repoti/models/utils.php');

		switch($a) {
			case 'timestamp':
				echo Utils::convertToTimestamp($_GET['datetime']);
				break;

			default:
				actionNotFound();
				break;
		}
		break;

	case 'reports':
		require_once('plugins/repoti/controllers/reports.php');
		header('Content-type: application/json');
		$r = new ReportController();
		switch($a) {
			case 'get':
				echo json_encode($r->getReportTemplates());
				break;

			case 'saveAsTemplate':
				$templateName = $_GET['templateName'];
				$rraTypeId = $_GET['rraTypeId'];
				$graphIds = $_GET['graphIds'];
				$beginPrime = $_GET['beginPrime'];
				$endPrime = $_GET['endPrime'];

				$r->saveAsTemplate($templateName, $rraTypeId, $graphIds, $beginPrime, $endPrime);
				break;

			case 'deleteTemplate':
				$r->deleteTemplate($_GET['id']);
				break;

		}
		break;

	default:
		echo 'Error: controller not found!';
		break;
}

function actionNotFound(){
	echo 'Error: action not found!';
}
?>
