<?php
chdir('../../');
require("include/auth.php");
require("IRG.php");
include("./include/top_graph_header.php");
include($config['base_path'] . "/plugins/irg/menu.php");
$api = IRG::getInstance();
?>

<link type="text/css" href="css/ui-lightness/jquery-ui-1.7.2.custom.css" rel="Stylesheet" />
<link type="text/css" href="css/style.css" rel="Stylesheet" />
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>

<script type="text/javascript" charset="utf-8">
    var dateFormat = 'yy/mm/dd';
    $(document).ready(function(){
        init();
    });

    function init(){
        $(".datepicker").datepicker(
            {
                changeMonth: true,
                changeYear: true,
                dateFormat: dateFormat,
                showOn: 'button',
                buttonImage: 'images/ui/calendar.gif',
                buttonImageOnly: true,
                numberOfMonths: 3,
                maxDate: 0
            });

        $(".selectButton").css('width', '120px');
        $(".select").css('width', '200px')
        .attr('size', '10');
        $(".data").sortable();

        function setRRATypes(data){
            rraTypes = data;
            endDateOnchange();
        }
        $.ajax({
            type: "GET",
            asyn: true,
            url:"Controller.php",
            data: {
                'a' : 'rra'
            },
            success: function(data){
                setRRATypes(data);
            },
            dataType: "json"
        });

        function setGraphs(data){
            graphs = data;
        }
        $.ajax({
            type: "GET",
            asyn: false,
            url: 'Controller.php',
            data: {
                'a' : 'graph'
            },
            success: function(data){
                setGraphs(data);
            },
            dataType: "json"
        });

        function setHosts(data){
            hosts = data;
        }
        $.ajax({
            url: "Controller.php",
            data: {
                'a' : 'host'
            },
            success: function(data){
                setHosts(data);
            },
            dataType: "json"
        });
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

        for(var i=0; i < rraTypes.length; i++){
            if(currentRRAType == rraTypes[i].id) {
                timespan = rraTypes[i].timespan;

                if(timespan >= 86400){
                    endDate = $("#endDate").val();
                    endDate = $.datepicker.parseDate(dateFormat, endDate);
                    endDate = $.datepicker.formatDate('@', endDate);
                    beginDate = endDate - (timespan * 1000);
                    beginDate = $.datepicker.parseDate('@', beginDate);
                    beginDate = $.datepicker.formatDate(dateFormat, beginDate);

                    $("#beginDate").val(beginDate);
                    $("#beginTime").val("00:00");
                    $("#endTime").val("00:00");
                } else{
                    endTime = $("#endTime").val();
                    endTime = endTime.split(":");

                    hours = Math.floor(timespan / 3600);
                    beginH = endTime[0] - hours;
                    minutes = timespan % 3600;
                    beginM = endTime[1] - minutes;

                    if(beginH < 0){
                        beginH = 24 - hours;
                    }
                    if(beginM < 0){
                        beginM = 60 - minutes;
                    }

                    beginH += "";
                    beginM += "";
                    if(beginH.length == 1){
                        beginH = "0" + beginH;
                    }
                    if(beginM.length == 1){
                        beginM = "0" + beginM;
                    }

                    $("#beginTime").val(beginH + ":" + beginM);
                    $("#beginDate").val($("#endDate").val());
                }
            }
        }
    }

    function preview(){
        $("#graphSelected *").attr('selected', 'selected');
        var rraType = $("#rraType").val();
        var graphIDs = $("#graphSelected").val();
        var hostIDs = new Array();

        for(i=0; i < graphIDs.length; i++){
            for(j=0; j < graphs.length; j++){
                if(graphIDs[i] == graphs[j].id){
                    var host_id = graphs[j].host_id;
                    if($.inArray(host_id, hostIDs) == -1){
                        hostIDs.push(graphs[j].host_id);
                    }
                }
            }
        }

        // report begin date
        var beginDate = $("#beginDate").val();
        var beginTime = $("#beginTime").val();
        // report end date
        var endDate = $("#endDate").val();
        var endTime = $("#endTime").val();
        // report prime time
        var beginPrime = $("#beginPrime").val();
        var endPrime = $("#endPrime").val();

        if(graphIDs != null){
            rraType = $("#rraType :selected").val();
            beginDateTime = beginDate + " " + beginTime;
            endDateTime = endDate + " " + endTime;

            function setTimeStamp(data){
                timeStamps = data;
            }
            $.ajax({
                type: "GET",
                async: false,
                url: "Controller.php",
                data: {
                    a: "timestamp",
                    beginDateTime: beginDateTime,
                    endDateTime: endDateTime
                },
                success: function(data){
                    setTimeStamp(data);
                },
                dataType: "json"
            });

            // Report configuration
            var conf = "<table>";
            for(var i=0; i < rraTypes.length; i++){
                if(rraType == rraTypes[i].id){
                    conf += "<tr><td><strong>RRA Type</strong></td><td>" + rraTypes[i].name + "<td></tr>";
                }
            }
            conf += "<tr><td><strong>Host IDs</strong></td><td>" + hostIDs.toString() + "<td></tr>";
            conf += "<tr><td><strong>Graph IDs</strong></td><td>" + graphIDs.toString() + "<td></tr>";
            conf += "<tr><td><strong>Begin Date</strong></td><td>" + beginDate + " " + beginTime + "<td></tr>";
            conf += "<tr><td><strong>End Date</strong></td><td>" + endDate + " " + endTime + "<td></tr>";
            conf += "<tr><td><strong>Begin Prime</strong></td><td>" + beginPrime + "<td></tr>";
            conf += "<tr><td><strong>End Prime</strong></td><td>" + endPrime + "<td></tr>";
            conf += "</table>";

            // Graph preview
            html = "";

            for (var i=0; i < graphIDs.length; i++) {
                $.ajax({
                    type: "GET",
                    async: false,
                    url: "Controller.php",
                    data: {
                        a: "graphReport",
                        graphID: graphIDs[i],
                        rraTypeID: rraType,
                        beginDateTime: timeStamps.beginTimeStamp,
                        endDateTime: timeStamps.endTimeStamp,
                        beginPrime: beginPrime,
                        endPrime: endPrime,
                    },
                    success: function(data){
                        html += "<h2 style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\">" + data.meta.title + "</h2>";
                        html += "<p><img  src=\"/cacti/graph_image.php?local_graph_id=";
                        html += data.meta.graph_id;
                        html += "&view_type=";
                        html += "&rra_id=" + rraType;
                        html += "&graph_start=" + timeStamps.beginTimeStamp
                        html += "&graph_end=" + timeStamps.endTimeStamp
                        html += "\" /></p>";

                        html += "<div class=\"data\">";
                        for(var i = 0; i < data.cols.length; i++){
                            //html += "<div style=\"position: static;\">";
                            html += "<table>";
                            html += "<tr><td><div style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\"><strong>TITLE</strong></div></td><td><div style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\">" + data.cols[i].title + "</div></td><br></tr>";
                            html += "<tr><td><div style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\"><strong>AVERAGE</strong></div></td><td>" + data.cols[i].avg + "</td><br></tr>";
                            html += "<tr><td><div style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\"><strong>PEAK</strong></div></td><td>" + data.cols[i].max + "</td><br></tr>";
                            html += "<tr><td><div style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\"><strong>PRIME TIME AVERAGE</strong></div></td><td>" + data.cols[i].p_avg + "</td><br></tr>";
                            html += "<tr><td><div style=\"background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;\"><strong>PRIME TIME PEAK</strong></div></td><td>" + data.cols[i].p_max + "</td><br></tr>";
                            html += "</table>";
                            //html += "</div>";
                        }

                        html += "</div>";
                    },
                    dataType: "json"
                });
            };

            $("#graphPreview").html(html);
            $("#reportConfig").html(conf);
            $("#preview").show();
            togglePanel();
        } else{
            $("#graphSelected")
            .css('background-color', 'pink')
            .css('border', '1px solid #999');
        }
    }

    function togglePanel(){
        $("#panel").toggle();

    }

    function clearButtonOnclick(){
        $("#graphPreview").html("");
        $("#reportConfig").html("");
        $("#preview").hide();
        $("#panel").show();
        resetOnclick();
    }

    function printPreview(){

    }

</script>
<p>
    <button id="togglePanelButton" onclick="togglePanel(); return false;">
        toggle make a report panel
    </button>
    <button id="clearButton" onclick="clearButtonOnclick(); return false;">clear</button>
</p>

<div class="irg" id="panel" style="padding-left:5px">
<h1>Make a report</h1>
<p>
    <table>
        <tr>
            <td valign="top">
                <h2>Report Type</h2>
                <select name="rraType" id="rraType" onchange="endDateOnchange(); return false;">
                <?php $rra_types = $api->getCactiRRAType(); ?>
                    <?php foreach ($rra_types as $rra_type): ?>
                        <option value="<?php echo $rra_type['id']; ?>"><?php echo $rra_type['name']; ?></option>
                    <?php endforeach ?>
                </select>
            </td>
            <td valign="top">
                <h2>Period Selection</h2>
                <h3>report period</h3>
                <table>
                    <tr>
                        <td>
                            <strong>Begin Date:</strong>
                        </td>
                        <td >
                            <input class="datepicker" type="text" name="beginDate" id="beginDate" value="<?php echo date("Y/m/d"); ?>"/>&nbsp;
                            <input type="text" name="beginTime" id="beginTime" value="<?php echo date("00:00"); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>End Date:</strong>
                        </td>
                        <td>
                            <input class="datepicker" type="text" name="endDate" id="endDate" onchange="endDateOnchange(); return false;" value="<?php echo date("Y/m/d"); ?>"/>&nbsp;
                            <input type="text" name="endTime" id="endTime" value="<?php echo date("00:00"); ?>" onchange="endDateOnchange(); return false;"/>
                        </td>
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
                        <td>
                            <strong>Begin Prime:</strong>
                        </td>
                        <td>
                            <strong>time format: H:i</strong><br>
                            <input class="" type="text" name="beginPrime" id="beginPrime" value="<?php echo date("00:00"); ?>"/>
                        </td>
                        <td>
                            <strong>End Prime:</strong>
                        </td>
                        <td>
                            <strong>time format: H:i</strong><br>
                            <input class="" type="text" name="endPrime" id="endPrime" value="<?php echo date("00:00"); ?>"/>
                        </td>
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
                <select class="select" name="hostSelect" id="hostSelect" multiple onchange="hostOnchange(); return false;">
            <?php foreach ($hosts as $host): ?>
                <option value="<?php echo $host['id']; ?>"><?php echo $host['hostname']; ?></option>
            <?php endforeach ?>
                </select>
            </td>
            <td>
                <h3>graphs</h3>
                <select class="select" name="graphSelect" id="graphSelect" multiple onchange="graphOnchange(); return false;">
                </select>
            </td>
            <td valign="center">
                <button class="selectButton" id="allHost" onclick="allHostOnclick(); return false;">
                    all host
                </button><br>
                <button class="selectButton" id="allGraph" onclick="allGraphOnclick(); return false;">
                    all graph
                </button><br>
                <button class="selectButton" id="addGraph" onclick="addGraphOnclick(); return false;">
                    &gt;&gt;
                </button><br>
                <button class="selectButton" id="removeGraph" onclick="removeGraphOnclick(); return false;">
                    &lt;&lt;
                </button><br>
                <button class="selectButton" id="resetSelect" onclick="resetOnclick(); return false;">
                    reset
                </button>
            </td>
            <td>
                <h3>selected graphs</h3>
                <select class="select" name="graphSelected" id="graphSelected" multiple>
                </select>
            </td>
        </tr>
    </table>
</p>

<table>
    <tr>
        <td valign="top">
            <input type="button" name="previewButton" value="preview" id="previewButton" onclick="preview(); return false;">
        </td>
    </tr>
</table>
</div>
<div id="preview" style="display: none;">
    <div style="float: left; margin: 5px;">
        <div style="background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;">
            <h2>Graphs Preview</h2>
        </div>
        <p id="graphPreview">

        </p>
    </div>
    <div style="float: left; margin: 5px;max-width: 350px;">
        <div style="background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;">
            <h2>Report Configuration</h2>
        </div>
        <p id="reportConfig">

        </p>
        <div style="background-color: #657AAA;padding-left: 5px;padding-right: 5px;color: white;">
            <h2>Summary</h2>
        </div>
        <p id="reportSummary">

        </p>
        <div>
            <p>

            </p>
        </div>
    </div>
</div>
