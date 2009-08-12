<?php
chdir("../../../");
include("include/auth.php");

chdir(dirname(__FILE__));
include('../irg_cacti_api.php');

$api = new IRG();

print_r($_SERVER);
?>