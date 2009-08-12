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
        } else{
            $sql = "SELECT * FROM `host` WHERE `id` = {$host_id}";
        }
        
        return db_fetch_assoc($sql);
    }
    
    public function getCactiGraph($host_id = 'all'){
        if($host_id == 'all'){
            $sql = "SELECT `graph_local`.`id`,              
			`graph_local`.`host_id`, `graph_templates_graph`.`title_cache`
            FROM `graph_templates_graph`
            JOIN `graph_local`
            ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`";
        } else{
            $sql = "SELECT `graph_local`.`id`,              
			`graph_local`.`host_id`, `graph_templates_graph`.`title_cache`
            FROM `graph_templates_graph`
            JOIN `graph_local`
            ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`
            WHERE `graph_local`.`host_id` = {$host_id}";
        }
        
        return db_fetch_assoc($sql);
    }
    
    public function getCactiRRAType(){
        $sql = "SELECT * FROM `rra`";
        
        return db_fetch_assoc("SELECT * FROM `rra`");
    }
    
    public function getExcludedPeriod($id='all'){
        
    }
    
    public function getReport($limit_start='0', $limit_end='30'){
        db_fetch_assoc("SELECT * FROM `irg_reports` LIMIT $limit_start, $limit_end");
    }
    
    public function getGraphData($graph_id, $rra_type_id, $graph_start, $graph_end){
        include_once("./lib/rrd.php");
        
        $graph_data = array();
        $graph_data['avg'] = 0;
        $graph_data['avg_p'] = 0;
        $graph_data['max'] = 0;
        $graph_data['min'] = 0; 

        $graph_data_array = array();
        $graph_data_array["graph_start"] = $graph_start;
        $graph_data_array["graph_end"] = $graph_end;

        $xport_array = rrdtool_function_xport($graph_id, $rra_type_id, $graph_data_array, $xport_meta);
        
        return $xport_array;
    }
}
?>
