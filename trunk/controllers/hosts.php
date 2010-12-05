<?php
class HostController {
    function getHosts() {
        $sql = "SELECT * FROM `host`";
        $hosts = db_fetch_assoc($sql);

        return $hosts;
    }

    function getHostById($id) {
        $sql = "SELECT * FROM `host` WHERE `id` = {$id}";
        $host = db_fetch_row($sql);

        return $host;
    }
}
?>
