<?php
class GraphController {
    function getGraphs() {
        $sql = "SELECT `graph_local`.`id`, `graph_local`.`host_id`,
        `graph_templates_graph`.`title_cache`, `graph_templates_graph`.`graph_template_id` AS `template_id`,
        `graph_templates_graph`.`base_value`
        FROM `graph_templates_graph`
        JOIN `graph_local`
        ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`";

        $graphs = db_fetch_assoc($sql);
        return $graphs;
    }

    function getTemplate($id) {
        $sql = "SELECT * FROM `graph_templates` WHERE `id` = {$id}";

        $template = db_fetch_row($sql);
        return $template;
    }

    function getGraphById($id) {
        $sql = "SELECT `graph_local`.`id`, `graph_local`.`host_id`,
        `graph_templates_graph`.`title_cache`, `graph_templates_graph`.`graph_template_id` AS `template_id`,
        `graph_templates_graph`.`base_value`
        FROM `graph_templates_graph`
        JOIN `graph_local`
        ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`
        WHERE `graph_local`.`id` = {$id}";

        $graphs = db_fetch_row($sql);
        return $graphs;
    }

    function getGraphByHostId($id) {
        $sql = "SELECT `graph_local`.`id`, `graph_local`.`host_id`,
        `graph_templates_graph`.`title_cache`
        FROM `graph_templates_graph`
        JOIN `graph_local`
        ON `graph_templates_graph`.`local_graph_id` = `graph_local`.`id`
        WHERE `graph_local`.`host_id` = {$id}";

        $graphs = db_fetch_assoc($sql);
        return $graphs;
    }

    function convertToSIUnit($value, $base_value) {
        $value = abs($value);
        $n = 0;

        while($value >= $base_value && $n <= 4) {
            $n++;
            $value = ($value / $base_value);
        }

        switch($n) {
            case '1':
                $n = 'k';
                break;
            case '2':
                $n = 'M';
                break;
            case '3':
                $n = 'G';
                break;
            case '4':
                $n = 'T';
                break;
            default:
                $n = '';
                break;
        }

        $value = sprintf("%.2f", $value);
        return "$value $n";
    }

    function getGraphStat($graphID, $rraTypeID, $graphStart, $graphEnd,
    $beginPrime, $endPrime) {
        include_once('./lib/rrd.php');
        include_once('./plugins/repoti/models/utils.php');

        $day = date('Y/m/d', $graphStart);
        $beginPrimeTimestamp = Utils::convertToTimestamp($day.' '.$beginPrime);
        $endPrimeTimestamp = Utils::convertToTimestamp($day.' '.$endPrime);

        $graphDataArray = array();
        $graphDataArray['graph_start'] = $graphStart;
        $graphDataArray['graph_end'] = $graphEnd;
        $exportArray = rrdtool_function_xport(
            $graphID,
            $rraTypeID,
            $graphDataArray,
            $exportMeta
        );

        $graphNote = array();
        for($i = 1; $i <= $exportArray['meta']['columns']; $i++) {
            $currCol = 'col'.$i;
            $graphNote[$currCol]['sum'] = 0;
            $graphNote[$currCol]['max'] = (float)-1.8e307;
            $graphNote[$currCol]['min'] = (float)1.8e307;
            $graphNote[$currCol]['count'] = 0;

            $graphNote[$currCol]['p_sum'] = 0;
            $graphNote[$currCol]['p_max'] = (float)-1.8e307;
            $graphNote[$currCol]['p_min'] = (float)1.8e307;;
            $graphNote[$currCol]['p_count'] = 0;

            $graphNote[$currCol]['e_sum'] = 0;
            $graphNote[$currCol]['e_min'] = (float)-1.8e307;;
            $graphNote[$currCol]['e_max'] = (float)1.8e307;;
            $graphNote[$currCol]['e_count'] = 0;
        }

        $data = $exportArray['data'];
        for($i = 1; $i <= $exportArray['meta']['rows']; $i++) {
            for($j = 1; $j <= $exportArray['meta']['columns']; $j++) {
                $currCol = 'col'.$j;

                if(is_numeric($data[$i][$currCol])) {
                    $val = abs($data[$i][$currCol]);

                    $val > $graphNote[$currCol]['max'] ?
                    $graphNote[$currCol]['max'] = $val : $graphNote[$currCol]['max'];
                    $val < $graphNote[$currCol]['min'] ?
                    $graphNote[$currCol]['min'] = $val : $graphNote[$currCol]['min'];
                    $graphNote[$currCol]['sum'] += $val;
                    $graphNote[$currCol]['count']++;

                    $dataTime = date('Hi', $data[$i]['timestamp']);

                    // Calculate prime time average this will not work on rra type that has 1 day average
                    if($dataTime >= date('Hi', $beginPrimeTimestamp) && $dataTime <= date('Hi', $endPrimeTimestamp)){

                        $val > $graphNote[$currCol]['p_max'] ?
                        $graphNote[$currCol]['p_max'] = $val : $graphNote[$currCol]['p_max'];
                        $val < $graphNote[$currCol]['p_min'] ?
                        $graphNote[$currCol]['p_min'] = $val : $graphNote[$currCol]['p_min'];
                        $graphNote[$currCol]['p_sum'] += $val;
                        $graphNote[$currCol]['p_count']++;
                    }
                }
            }
        }

        $graph = $this->getGraphById($graphID);
        $base_value = $graph['base_value'];

        // data to be return after calculate
        $returnData = array();
        $returnData['meta']['title'] = $graph['title_cache'];
        $returnData['meta']['graph_id'] = $graphID;

        //$returnData['meta']['day'] = $day;
        $returnData['meta']['begin_prime'] = date('H:i', $beginPrimeTimestamp);
        $returnData['meta']['end_prime'] = date('H:i', $endPrimeTimestamp);

        $returnData['meta']['graph_start'] =
        date('Y/m/d H:i', $exportArray['meta']['start']);
        $returnData['meta']['graph_end'] =
        date('Y/m/d H:i', $exportArray['meta']['end']);

        $cols = array();
        for($i = 1; $i <= $exportArray['meta']['columns']; $i++){
            $currCol = 'col'.$i;
            $cols[$i-1]['title'] = $exportArray['meta']['legend'][$currCol];

            $cols[$i-1]['avg'] = $graphNote[$currCol]['count'] == 0
            ? 0 : $this->convertToSIUnit(($graphNote[$currCol]['sum'] / $graphNote[$currCol]['count']), $base_value);
            $cols[$i-1]['max'] = $this->convertToSIUnit((float)$graphNote[$currCol]['max'], $base_value);
            $cols[$i-1]['min'] = $this->convertToSIUnit((float)$graphNote[$currCol]['min'], $base_value);

            $cols[$i-1]['p_avg'] = $graphNote[$currCol]['p_count'] == 0
            ? 0 : $this->convertToSIUnit(($graphNote[$currCol]['p_sum'] / $graphNote[$currCol]['p_count']), $base_value);
            $cols[$i-1]['p_max'] = $this->convertToSIUnit((float)$graphNote[$currCol]['p_max'], $base_value);
            $cols[$i-1]['p_min'] = $this->convertToSIUnit((float)$graphNote[$currCol]['p_min'], $base_value);

            $cols[$i-1]['e_avg'] = $graphNote[$currCol]['e_count'] ==
            0 ? 0 : $this->convertToSIUnit(($graphNote[$currCol]['e_sum'] / $graphNote[$currCol]['e_count']), $base_value);
            $cols[$i-1]['e_max'] = $this->convertToSIUnit((float)$graphNote[$currCol]['e_max'], $base_value);
            $cols[$i-1]['e_min'] = $this->convertToSIUnit((float)$graphNote[$currCol]['e_min'], $base_value);
        }

        $returnData['cols'] = $cols;
        return $returnData;
    }
}
?>
