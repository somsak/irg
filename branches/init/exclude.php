<?php
chdir('../../');
require("include/auth.php");
require("irg_cacti_api.php");
// include("./include/top_graph_header.php");
// include($config['base_path'] . "/plugins/irg/menu.php"); 

header("Content-Type: text/plain");

$api = IRG::getInstance();
print_r($api->getGraphData(21, 1, 1249319497, 1249405597));
?>