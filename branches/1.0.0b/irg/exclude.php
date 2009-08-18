<?php
chdir('../../');
require("include/auth.php");
require("IRG.php");
// include("./include/top_graph_header.php");
// include($config['base_path'] . "/plugins/irg/menu.php");

header("Content-Type: text/plain");

$api = IRG::getInstance();
$reportDataArray['graph_id'] = 1;
$reportDataArray['rra_type_id'] = 3;
# Use to calulate overall value
$reportDataArray['graph_start'] = '';
$reportDataArray['graph_end'] = '';
# Use to calculate prime time value
$reportDataArray['prime_start'] = 1000;
$reportDataArray['prime_end'] = 1100;

$api->getReportData($reportDataArray);
?>
