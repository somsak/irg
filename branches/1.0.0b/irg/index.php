<?php
    $pathInfo = $_SERVER['PATH_INFO'];
    $i = split("/", $pathInfo);
    
    echo dirname(__FILE__);
?>