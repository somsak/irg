<?php
class IRG{
    private static $instance;

    private function __construct(){

    }

    public static function getInstance(){
        if (!isset(self::$instance)){
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function getCactiHost($host_id = 'all'){
        if($host_id == 'all'){
            $sql = "SELECT * FROM `host`";
            return db_fetch_assoc($sql);
        } else{
            $sql = "SELECT * FROM `host` WHERE `id` = {$host_id}";
            return db_fetch_row($sql);
        }
    }

    public function getCactiGraph($host_id = 'all'){
        if($host_id == 'all'){
            $sql = "SELECT `graph_local`.`id`,
            `graph_local`.`host_id`, `graph_templates_graph`.`title_cache`
            FROM `graph_templates_graph`
            JOIN `graph_local`
            ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`";
            return db_fetch_assoc($sql);
        } else{
            $sql = "SELECT `graph_local`.`id`,
            `graph_local`.`host_id`, `graph_templates_graph`.`title_cache`
            FROM `graph_templates_graph`
            JOIN `graph_local`
            ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`
            WHERE `graph_local`.`host_id` = {$host_id}";
            return db_fetch_row($sql);
        }


    }

    public function getCactiRRAType($id='all'){
        if($id == 'all'){
            $sql = "SELECT * FROM `rra`";
            return db_fetch_assoc($sql);
        } else{
            $sql = "SELECT * FROM `rra` WHERE `id` = {$id}";
            return db_fetch_row($sql);
        }
    }

    public function getExcludedPeriod($id='all'){

    }

    public function saveExcludedPeriod($data){

    }

    public function getReportByID($id) {
        db_fetch_assoc("SELECT * FROM `irg_reports` WHERE `id`= {$id}");
    }

    public function getReport($limit_start='0', $limit_end='30'){
        db_fetch_assoc("SELECT * FROM `irg_reports` LIMIT $limit_start, $limit_end");
    }

    public function saveReport($data){

    }

    public function getReportDate($reportDataArray){
        include_once("./lib/rrd.php");

        $reportDataArray['graph_id'];
        $reportDataArray['rra_type_id'];
        # Use to calulate overall value
        $reportDataArray['graph_start'];
        $reportDataArray['graph_end'];
        # Use to calculate prime time value
        $reportDataArray['prime_start'];
        $reportDataArray['prime_end'];

        $graph_data = array();

        # data to be return after calculate
        $graph_data['avg'] = 0;
        $graph_data['avg_p'] = 0;
        $graph_data['max'] = 0;
        $graph_data['min'] = 0;

        $graph_data['e_avg'] = 0;
        $graph_data['e_avg_p'] = 0;
        $graph_data['e_max'] = 0;
        $graph_data['e_min'] = 0;

        $graph_data_array = array();
        $graph_data_array["graph_start"] = $graph_start;
        $graph_data_array["graph_end"] = $graph_end;

        $xport_array = rrdtool_function_xport($graph_id, $rra_type_id, $graph_data_array, $xport_meta);

        return $xport_array;
    }

    public function getTimeStamp($dateTime){
        # 2000/08/25 23:30
        $val = explode(" ",$dateTime);
        $date = explode("/",$val[0]);
        $time = explode(":",$val[1]);

        return substr(mktime($time[0], $time[1], 0, $date[1], $date[2], $date[0]), 0, 10);
    }
}
?>
