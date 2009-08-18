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

    public function getCactiGraphInfo($graph_id){
        $sql = "SELECT * FROM `graph_templates_graph` WHERE `local_graph_id` = {$graph_id}";
        return db_fetch_row($sql);
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

    public function getReportData($reportDataArray){
        include_once("./lib/rrd.php");

        $graphDataArray = array();
        $graph_data_array["graph_start"] = $reportDataArray['graph_start'];
        $graph_data_array["graph_end"] = $reportDataArray['graph_end'];

        $beginPrime = $reportDataArray['begin_prime'];
        $beginPrime = explode(':', $beginPrime);
        $beginPrime = $beginPrime[0].$beginPrime[1];

        $endPrime = $reportDataArray['end_prime'];
        $endPrime = explode(':', $endPrime);
        $endPrime = $endPrime[0].$endPrime[1];


        $return_data_array = array();

        $xport_array = rrdtool_function_xport(
            $reportDataArray['graph_id'],
            $reportDataArray['rra_type_id'],
            $graph_data_array,
            $xport_meta
        );

        //print_r($xport_array['meta']);

        // Prepare data array
        $legends = $xport_array['meta']['legend'];

        // note
        $graph_note = array();
        for($i = 1; $i <= $xport_array['meta']['columns']; $i++){
            $currCol = 'col'.$i;
            $graph_note[$currCol]['sum'] = 0;
            $graph_note[$currCol]['max'] = (float)-1.8e307;
            $graph_note[$currCol]['min'] = (float)1.8e307;
            $graph_note[$currCol]['count'] = 0;

            $graph_note[$currCol]['p_sum'] = 0;
            $graph_note[$currCol]['p_max'] = (float)-1.8e307;
            $graph_note[$currCol]['p_min'] = (float)1.8e307;;
            $graph_note[$currCol]['p_count'] = 0;

            $graph_note[$currCol]['e_sum'] = 0;
            $graph_note[$currCol]['e_min'] = (float)-1.8e307;;
            $graph_note[$currCol]['e_max'] = (float)1.8e307;;
            $graph_note[$currCol]['e_count'] = 0;
        }


        $data = $xport_array['data'];
        for($i = 1; $i <= $xport_array['meta']['rows']; $i++){
            $dataTime = date("Hi", $data[$i]['timestamp']);

            for($j = 1; $j <= $xport_array['meta']['columns']; $j++){
                $currCol = 'col'.$j;

                if($data[$i][$currCol] != 'NaN'){
                    $val = abs($data[$i][$currCol]);

                    $val > $graph_note[$currCol]['max'] ? $graph_note[$currCol]['max'] = $val : $graph_note[$currCol]['max'];
                    $val < $graph_note[$currCol]['min'] ? $graph_note[$currCol]['min'] = $val : $graph_note[$currCol]['min'];
                    $graph_note[$currCol]['sum'] += $val;
                    $graph_note[$currCol]['count']++;

                    if($dataTime >= $beginPrime && $dataTime <= $endPrime){
                        $val > $graph_note[$currCol]['p_max'] ? $graph_note[$currCol]['p_max'] = $val : $graph_note[$currCol]['p_max'];
                        $val < $graph_note[$currCol]['p_min'] ? $graph_note[$currCol]['p_min'] = $val : $graph_note[$currCol]['p_min'];
                        $graph_note[$currCol]['p_sum'] += $val;
                        $graph_note[$currCol]['p_count']++;
                    }
                }
            }
        }

        // graph base value
        $graph = $this->getCactiGraphInfo($reportDataArray['graph_id']);

        // data to be return after calculate
        $return_data = array();
        $return_data['meta']['title'] = $graph['title_cache'];
        $return_data['meta']['graph_id'] = $reportDataArray['graph_id'];
        $return_data['meta']['base_value'] = $graph['base_value'];
        $return_data['meta']['begin_prime'] = $beginPrime;
        $return_data['meta']['end_prime'] = $endPrime;
        $return_data['meta']['graph_start'] = date('Y/m/d H:i', $xport_array['meta']['start']);
        $return_data['meta']['graph_end'] = date('Y/m/d H:i', $xport_array['meta']['end']);

        $cols = array();
        for($i = 1; $i <= $xport_array['meta']['columns']; $i++){
            $currCol = 'col'.$i;
            $cols[$i -1]['title'] = $xport_array['meta']['legend'][$currCol];

            $cols[$i -1]['avg'] = $graph_note[$currCol]['count'] == 0 ? 0 : ($graph_note[$currCol]['sum'] / $graph_note[$currCol]['count']) / $return_data['meta']['base_value'];
            $cols[$i -1]['max'] = (float)$graph_note[$currCol]['max'];
            $cols[$i -1]['min'] = (float)$graph_note[$currCol]['min'];

            $cols[$i -1]['p_avg'] = $graph_note[$currCol]['p_count'] == 0 ? 0 : ($graph_note[$currCol]['p_sum'] / $graph_note[$currCol]['p_count']) / $return_data['meta']['base_value'];
            $cols[$i -1]['p_max'] = (float)$graph_note[$currCol]['p_max'];
            $cols[$i -1]['p_min'] = (float)$graph_note[$currCol]['p_min'];

            $cols[$i -1]['e_avg'] = $graph_note[$currCol]['e_count'] == 0 ? 0 : ($graph_note[$currCol]['e_sum'] / $graph_note[$currCol]['e_count']) /  $return_data['meta']['base_value'];
            $cols[$i -1]['e_max'] = (float)$graph_note[$currCol]['e_max'];
            $cols[$i -1]['e_min'] = (float)$graph_note[$currCol]['e_min'];
        }

        $return_data['cols'] = $cols;

        //print_r($return_data);
        return $return_data;
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
