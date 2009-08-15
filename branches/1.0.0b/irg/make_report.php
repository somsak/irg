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

        $(".selectButton").css('width', '80px');
        $(".select").css('width', '200px')
        .attr('size', '10');

        function setRRATypes(data){
            rraTypes = data;
            endDateOnchange();
        }
        $.ajax({
            type: "GET",
            asyn: true,
            url:"irg_cacti_controler.php",
            data: {
                'a' : 'rra'
            },
            success: function(data){
                setRRATypes(data);
                return;
            },
            dataType: "json"
        });

        function setGraphs(data){
            graphs = data;
        }
        $.ajax({
            type: "GET",
            asyn: false,
            url: 'irg_cacti_controler.php',
            data: {
                'a' : 'graph'
            },
            success: function(data){
                setGraphs(data);
                return;
            },
            dataType: "json"
        });

        function setHosts(data){
            hosts = data;
        }
        $.ajax({
            url: "irg_cacti_controler.php",
            data: {
                'a' : 'host'
            },
            success: function(data){
                setHosts(data);
                return;
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

                    return;
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

                    return;
                }
            }
        }
    }

    function makeReport(){
        $("#graphSelected *").attr('selected', 'selected');
        var rraType = $("#rraType").val();
        var graphIDs = $("#graphSelected").val();

        var beginDate = $("#beginDate").val();
        var beginTime = $("#beginTime").val();
        var endDate = $("#endDate").val();
        var endTime = $("#endTime").val();

        var beginPrime = $("#beginPrime").val();
        var endPrime = $("#endPrime").val();

        if(graphIDs != null){
            var conf = "";
            conf += "<strong>RRA Type</strong>: " + rraType + "<br>";
            conf += "<strong>Graphs</strong>: " + graphIDs.toString() + "<br>";
            conf += "<strong>Begin Date: </strong>" + beginDate + "<br>";
            conf += "<strong>End Date: </strong>" + endDate + "<br>";

            rraType = $("#rraType :selected").val();
            beginDateTime = beginDate + " " + beginTime;
            endDateTime = endDate + " " + endTime;

            function setTimeStamp(data){
                timeStamps = data;
            }
            $.ajax({
                type: "GET",
                async: false,
                url: "irg_cacti_controler.php",
                data: "a=t&beginDateTime="+beginDateTime+"&endDateTime="+endDateTime,
                success: function(data){
                    setTimeStamp(data);
                    return;
                },
                dataType: "json"
            });

            html = "";
            for (var i=0; i < graphIDs.length; i++) {
                html += "<p><img  src=\"/cacti/graph_image.php?local_graph_id=";
                html += graphIDs[i];
                html += "&view_type=tree";
                html += "&rra_id=" + rraType;
                html += "&graph_start=" + timeStamps.beginTimeStamp
                html += "&graph_end=" + timeStamps.endTimeStamp
                html += "\" /></p>";
            };

            $("#reportConf").html(html);

        } else{
            $("#graphSelected")
            .css('background-color', 'pink')
            .css('border', '1px solid #999');
        }
        togglePanel();
    }

    function togglePanel(){
        $("#panel").toggle();
    }

</script>
<p>
    <button id="togglePanelButton" onclick="togglePanel()">toggle make a report panel</button>
</p>
<h1>Make a report</h1>

<div class="irg" id="panel" style="padding-left:5px">
<p>
    <table>
        <tr>
            <td valign="top">
                <h2>Report Type</h2>
                <select name="rraType" id="rraType" onchange="endDateOnchange()">
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
                            <input class="datepicker" type="text" name="endDate" id="endDate" onchange="endDateOnchange()" value="<?php echo date("Y/m/d"); ?>"/>&nbsp;
                            <input type="text" name="endTime" id="endTime" value="<?php echo date("00:00"); ?>" onchange="endDateOnchange()"/>
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
                <button class="selectButton" id="allHost" onclick="allHostOnclick()">
                    all host
                </button><br>
                <button class="selectButton" id="allGraph" onclick="allGraphOnclick()">
                    all graph
                </button><br>
                <button class="selectButton" id="addGraph" onclick="addGraphOnclick()">
                    &gt;&gt;
                </button><br>
                <button class="selectButton" id="removeGraph" onclick="removeGraphOnclick()">
                    &lt;&lt;
                </button><br>
                <button class="selectButton" id="resetSelect" onclick="resetOnclick()">
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
            <input type="button" name="make_button" value="make" id="make_button" onclick="makeReport()">
        </td>
    </tr>
</table>
</div>
<p id="reportConf">

</p>

