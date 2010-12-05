<?php
class ReportController {
    function getReportTemplates() {
        $sql = "SELECT * FROM `repoti_template`";

        return db_fetch_assoc($sql);
    }

    function saveAsTemplate($templateName, $rraTypeId, $graphIds, $beginPrime, $endPrime) {
        $data = array();
        $data['id'] = '';
        $data['template_name'] = $templateName;
        $data['rratype_id'] = $rraTypeId;
        $data['graph_ids'] = $graphIds;
        $data['begin_prime'] = $beginPrime;
        $data['end_prime'] = $endPrime;

        sql_save($data, 'repoti_template');
    }

    function deleteTemplate($id) {
        $sql = "DELETE FROM `repoti_template` WHERE `id` = {$id}";

        db_execute($sql);
    }
}
?>
