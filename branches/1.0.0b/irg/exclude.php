<?php
chdir('../../');
require("include/auth.php");
require("IRG.php");
// include("./include/top_graph_header.php");
// include($config['base_path'] . "/plugins/irg/menu.php");

header("Content-Type: text/plain");

$api = IRG::getInstance();
print_r($api->getReportData());
?>
