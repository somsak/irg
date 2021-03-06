<?php include('load.php');?>
<script type="text/javascript" src="make_report.js"></script>
<div id="repoti">
    <div id="repoti-left" class="ui-corner-all">
        <div id="left-menu" class="">
            <div class="h3-bg-1 hide-item"><h3>Report configuration</h3></div>
            <div class="content-1 hide-item">
                <div>
                    <table class="repoti">
                            <tr class="repoti">
                                <th class="repoti">RRA type:</th>
                                <td class="repoti"><span id="conf-rra-type-id"></span></td>
                            </tr>
                            <tr class="repoti">
                                <th class="repoti">End date:</th>
                                <td class="repoti"><span id="conf-end-date"></span></td>
                            </tr>
                             <tr class="repoti">
                                <th class="repoti">End time:</th>
                                <td class="repoti"><span id="conf-end-time"></span></td>
                            </tr>
                            <tr>
                                <th class="repoti">Begin prime time:</th>
                                <td class="repoti"><span id="conf-begin-prime-time"></span></td>
                            </tr>
                            <tr>
                                <th class="repoti">End prime time:</th>
                                <td class="repoti"><span id="conf-end-prime-time"></span></td>
                            </tr>
                    </table>
                </div>
            </div>
            <div>
                <div id="more-action-menu" class="hide-item">
                    <div class="h3-bg-1"><h3>Templates</h3></div>
                    <div style="padding: 3px; text-align: left;">
                        <select id="template-id" style="width: 90%;"></select>
                        <table>
                            <tr>
                                <td><button id="load-template">load</button></td>
                                <td><button id="delete-template">delete</button></td>
                            </tr>
                        </table>
                    </div>
                    <div style="padding: 3px; text-align: left;">
                        <span>name: </span><input id="template-name" type="text" style="width: 90%;"/><br>
                    </div>
                    <button id="save-as-template" >save as template</button>
                </div>
            </div>
            <div class="h3-bg-1 hide-item"><h3>Configure the report</h3></div>
            <div class="content-1 hide-item">
                <div>
                <form>
                    <table class="repoti">
                        <tr class="repoti">
                            <th class="repoti"><span>Report rra type:</span> </th>
                            <td class="repoti"><select id="report-rra-type-id" name="report-rra-type-id"></select></td>
                        </tr>
                        <tr class="repoti">
                            <th class="repoti"><span>Report end date:</span></th>
                            <td class="repoti"><input id="report-end-date" name="report-end-date" type="text" value="<?php echo date("Y/m/d"); ?>"/></td>
                        </tr>
                        <tr class="repoti">
                            <th class="repoti"><span>Report end time:</span></th>
                            <td class="repoti"><input id="report-end-time" name="report-end-time" type="text" value="00:00"/><span style="padding-left: 3px;">format H:i</span></td>
                        </tr>
                        <tr>
                            <th class="repoti"><span>Begin prime time:</span></th>
                            <td class="repoti"><input id="report-begin-prime-time" name="report-begin-prime-time" type="text" value="08:00"/><span style="padding-left: 3px;">format H:i</span></td>
                        </tr>
                        <tr>
                            <th class="repoti"><span>End prime time:</span></th>
                            <td class="repoti"><input id="report-end-prime-time" name="report-end-prime-time" type="text" value="12:00"/><span style="padding-left: 3px;">format H:i</span></td>
                        </tr>
                    </table>
                </form>
                </div>
            </div>            
            <div id="host-and-graph" class="hide-item">
	            <div class="h3-bg-1"><h3>Host and Graph</h3></div>
	            <div class="content-2">
	                <div id="collapse-expend-host-graph" class="col-ex-ul">Collapse / Expand</div><br>
	                <div><ul id="graph-select"></ul></div>
	                <button id="report-update">Update</button>
	            </div>
            </div>
        </div>
    </div>
    <div id="repoti-right" class="ui-corner-all">
        <div id="main">
            <div class="h3-bg-1"><h3>Preview & Edit</h3>
            <div class="content-2">
                <div>
                    <ul id="preview"></ul>
                </div>
            </div>
        </div>
    </div>
    <div style="position: fixed; top: 0; right: 0;z-index: 101;" >
        <button onclick="make_report.toggle(); return false;">toggle</button>
    </div>
</div>
</body>
</html>
