<?php
include('load.php');
?>
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
    var hosts = Host.getHosts();
    $("#hostOl").append(Host.HTML.ol(hosts));
});
</script>
<div id="repoti">
    <div id="repoti-left">
    </div>
    <div id="repoti-right">
        <div id="hostOl"></div>
    </div>
</div>
