<?php
chdir('../../');
require("include/auth.php");
require("irg_cacti_api.php");
include("./include/top_graph_header.php");
include($config['base_path'] . "/plugins/irg/menu.php");
$api = IRG::getInstance();

?>
<link type="text/css" href="css/ui-lightness/jquery-ui-1.7.2.custom.css" rel="Stylesheet" />
<link type="text/css" href="css/style.css" rel="Stylesheet" />
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>

<script type="text/javascript" charset="utf-8">
    var rraTypes, hosts, graphs;
    
    $(document).ready(function(){
        init()
    });
    
    function init(){
        $(".datepicker").datepicker(
            {
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy-mm-dd',
                showOn: 'button',
                buttonImage: 'images/ui/calendar.gif',
                buttonImageOnly: true
            }).css('background-color', '#EDDA74');
            
            
        $(".selectButton").css('width', '80px');
        $(".select").css('width', '200px')
        .attr('size', '10');
        
        $.get('irg_cacti_controler.php', {
            'a' : 'graph'
        }, function(data){
            setGraphs(data);
        });
    }
    
    function setGraphs(data){
        graphs = eval(data);
    }
    
    function setHosts(data){
        hosts = eval(data);
    }
    
    function setRRATypes(data){
        rraTypes = eval(data);
    }
    
    function getCheckedHosts(){
        return $("#hostSelect :selected");
    }
    
    function getCheckedGraphs(){
        return $("#graphSelect :selected"); 
    }
    
    function hostOnchange(){
        var checked_hosts = getCheckedHosts();
        var host_ids = new Array();
        
        for (var i=0; i < checked_hosts.length; i++){
            host_ids[i] = $(checked_hosts[i]).val();
        };
        
        var opts = "";
        var graphSelect = $("#graphSelect");
        graphSelect.empty();
        
        var graphSelected = $("#graphSelected *");
        var arr = new Array();
        for (var i=0; i < graphSelected.length; i++) {
            arr[i] = $(graphSelected[i]).val();
        };
        
        for (var i=0; i < graphs.length; i++) {
            if($.inArray(graphs[i].host_id, host_ids) != -1 
            && $.inArray(graphs[i].id, arr) == -1){
                opts += '<option value="' + graphs[i].id + '">' + graphs[i].title_cache + '</option>\n';
                }
        };
        
        graphSelect.append(opts);
    }
    
    function graphOnchange(){
        var checked_graphs = getCheckedGraphs();
        var graph_ids = new Array();
        
        for (var i=0; i < checked_graphs.length; i++){
            graph_ids[i] = checked_graphs[i].getAttribute('value');
        };
    }
    
    function allHostOnclick(){
        $("#hostSelect *").attr('selected', 'selected');
        hostOnchange();
    }
    
    function allGraphOnclick(){
        $("#graphSelect *").attr('selected', 'selected');
    }
    
    function addGraphOnclick(){
        $("#graphSelected").append($("#graphSelect :selected").attr('selected', '').remove());
        
    }
    
    function removeGraphOnclick(){
        $("#graphSelect").append($("#graphSelected :selected").attr('selected', '').remove());
        hostOnchange();
    }
    
    function resetOnclick(){
        $("#hostSelect *").attr('selected', '');
        $("#graphSelect").append($("#graphSelected *").attr('selected', '').remove());
        hostOnchange();
    }
    
    function endDateOnchange(){
        /*
            TODO get rraType id and update beginDate value use rraType's timespan to calculate beginDate
        */
        var currentRRAType = $("#rraType").val();
        
        switch(currentRRAType){
            case '1':
                break;
            case '2':
                break;
            case '3':
                break;
            case '4':
                break;
            case '5':
                break;
            default:
                alert('Error: unknown RRA type!');
        }
        
    }
    
    function makeReport(){
        $("#graphSelected *").attr('selected', 'selected');
        var rraType = $("#rraType").val();
        var graphIDs = $("#graphSelected").val();
        
        var beginDate = $("#beginDate").val();
        var endDate = $("#endDate").val();
        
        if(graphIDs != null){
            var conf = "";
            conf += "<strong>RRA Type</strong>: " + rraType + "<br>";
            conf += "<strong>Graphs</strong>: " + graphIDs.toString() + "<br>";
            conf += "<strong>Begin Date: </strong>" + beginDate + "<br>";
            conf += "<strong>End Date: </strong>" + endDate + "<br>";
            
            html = "";                                                
            beginDate = $.datepicker.parseDate('yy-mm-dd', beginDate);
            beginDate = $.datepicker.formatDate('@', beginDate);
            endDate = $.datepicker.parseDate('yy-mm-dd', endDate);
            endDate = $.datepicker.formatDate('@', endDate);
            
            for (var i=0; i < graphIDs.length; i++) {
                html += "<img  src=\"/cacti/graph_image.php?local_graph_id=";
                html += graphIDs[i];
                html += "&rra_id=3";
                html += "&graph_start=" 
                + beginDate.substring(0,10);
                html += "&graph_end=" 
                + endDate.substring(0,10);
                html += "\" />";
            };

            $("#reportConf").html(html);
            
        } else{
            $("#graphSelected")
            .css('background-color', 'pink')
            .css('border', '1px solid #999');
        } 
    }
</script>
<div class="irg" style="padding-left:5px">
<h1>Make a report</h1>
<p>
    <table>
        <tr>
            <td width="225px" valign="top" style="border: 2px solid red;">
                <h2>Report Type</h2>
                &nbsp;&nbsp;&nbsp;<select name="rraType" id="rraType" onchange="">
                <?php $rra_types = $api->getCactiRRAType(); ?>
                <?php foreach ($rra_types as $rra_type): ?>
                    <option value="<?php echo $rra_type['id']; ?>"><?php echo $rra_type['name']; ?></option>
                <?php endforeach ?>
                </select>
            </td>
            <td valign="top">
                <h2>Period Selection</h2>
                <p>&#x2190;Begin Date depend on report type</p>
                <h3>report period</h3>
                <table>
                    <tr>
                        <td><strong>Begin Date:</strong></td>
                        <td><input class="datepicker" type="text" name="beginDate" id="beginDate" value="<?php echo date("Y-m-d"); ?>"/></td>
                        <td><strong>End Date:</strong></td>
                        <td><input class="datepicker" type="text" name="endDate" id="endDate" onchange="endDateOnchange()" value="<?php echo date("Y-m-d"); ?>"/></td>                                                              
                    </tr>  
                </table>      
            </td>
        </tr>
        <tr>
            <td>
                
            </td>
            <td>
                <h3>prime time period</h3>
                <table>
                    <tr>
                        <td><strong>Begin Time:</strong></td>
                        <td><input class="datepicker" type="text" name="beginDate" id="beginDate" value="<?php echo date("Y-m-d"); ?>"/></td>
                        <td><strong>End Time:</strong></td>
                        <td><input class="datepicker" type="text" name="endDate" id="endDate" onchange="endDateOnchange()" value="<?php echo date("Y-m-d"); ?>"/></td>
                    </tr>
                </table>              
            </td>
        </tr>
    </table>
</p>
<p>
    <h2>Select hosts and graphs</h2>
    <?php $hosts = $api->getCactiHost(); ?>
    <table border="0" cellspacing="5" cellpadding="5">
        <tr>
            <td>
                <h3>hosts</h3>
                <select class="select" name="hostSelect" id="hostSelect" multiple onchange="hostOnchange()">
            <?php foreach ($hosts as $host): ?>
                <option value="<?php echo $host['id']; ?>"><?php echo $host['hostname']; ?></option>
            <?php endforeach ?>
                </select>
            </td>
            <td>
                <h3>graphs</h3>
                <select class="select" name="graphSelect" id="graphSelect" multiple onchange="graphOnchange()">
                </select>
            </td>
            <td valign="center">
                <button class="selectButton" id="allHost" onclick="allHostOnclick()">all host</button><br>
                <button class="selectButton" id="allGraph" onclick="allGraphOnclick()">all graph</button><br>
                <button class="selectButton" id="addGraph" onclick="addGraphOnclick()">&gt;&gt;</button><br> 
                <button class="selectButton" id="removeGraph" onclick="removeGraphOnclick()">&lt;&lt;</button><br>
                <button class="selectButton" id="resetSelect" onclick="resetOnclick()">reset</button>
            </td>
            <td>
                <h3>selected graphs</h3>
                <select class="select" name="graphSelected" id="graphSelected" multiple>
                </select>
            </td>
        </tr>
    </table>
</p>

<table width="100%">
    <tr>
        <td valign="top">
            <input type="button" name="make_button" value="continue" id="make_button" onclick="makeReport()">
        </td>
        <td>
            <p id="reportConf"></p>
        </td>
    </tr>
</table>
</div>