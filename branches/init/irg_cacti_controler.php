<?php
chdir('../../');
require("include/auth.php");
require("irg_cacti_api.php");

header("Content-type: application/json"); 
$api = IRG::getInstance();
$a = $_GET['a'];

switch ($a) {
    case 'graph':
        $graphs = $api->getCactiGraph();
        echo json_encode($graphs);
        break;
	case 'host':
		$hosts = $api->getCactiHost();
		echo json_encode($hosts);
    default:
        echo "Error: action not found!";
        break;
}
?>
