// Graph objects
function Graph(graph) {
    var id = graph.id;
    var hostId = graph.host_id;
    var title = graph.title_cache;
    var templateId = graph.template_id;

    this.getGraphId = function() {
        return id;
    }

    this.getHostId = function() {
        return hostId;
    }

    this.getTitle = function() {
        return title;
    }

    this.getTemplateId = function() {
        return templateId;
    }

    this.getImageUrl = function(rraType, graphStart, graphEnd) {
        return "/cacti/graph_image.php?local_graph_id=" + id + "&view_type=tree"
                + "&rra_id=" + rraType.getRRAId() + "&graph_start=" + graphStart
                + "&graph_end=" + graphEnd;
    }
}

Graph.getGraphs = function(hostId) {
    var graphs = new Array();

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "graphs",
            a: "getByHostId",
            hostId: hostId,
        },
        success: function(data){
            for(d in data){
                graphs[data[d].id] = new Graph(data[d]);
            }
        },
        dataType: "json",
    });

    return graphs;
}

Graph.getGraphById = function(graphId) {
    var graph;

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "graphs",
            a: "getById",
            graphId: graphId,
        },
        success: function(data){
            graph = new Graph(data);
        },
        dataType: "json",
    });

    return graph;
}

Graph.getTemplate = function(templateId) {
    var template;

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "graphs",
            a: "getTemplate",
            templateId: templateId,
        },
        success: function(data){
            template = data.name;
        },
        dataType: "json",
    });

    return template;
}

Graph.HTML = {};
Graph.HTML.li_select = function(hosts) {
    var html = "";
    for(h in hosts) {
        var host = hosts[h];
        html += "<li>";
        html += "<div class='host-desc'>" + host.getDescription() + "</div><div class='drag'>&uarr;&darr</div>";
        html += "<ul class='graph-list'>";
        var graphs = hosts[h].getGraphs();
        for(g in graphs) {
            var graph = graphs[g];
            html += "<li>";
            html += "<input class='graph-checkbox' type='checkbox' value='" + graph.getGraphId() +"'/>";
            html += graph.getTitle();
            html += "<div style='float: right;' class='drag'>&uarr;&darr;</div></li>";
        }
        html += "</ul>";
        html += "<hr>";
        html += "</li>";
    }

    return html;
}

Graph.HTML.getGraphStatTables = function(graph, report) {
    var rraType = report.getRRAType();
    var html = "";
    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "graphs",
            a: "getstat",
            graphId : graph.getGraphId(),
            rraTypeId : report.getRRAType().getRRAId(),
            timespan: rraType.getTimespan(),
            graphStart : report.getGraphBeginTimestamp(),
            graphEnd : report.getGraphEndTimestamp(),
            beginPrime : report.getBeginPrimeTime(),
            endPrime : report.getEndPrimeTime(),
        },
        success: function(data){
            html = "";
            html += "<table class='graph-stat'>";
            html += "<tr><th>value</th><th>Average</th><th>Peak</th><th>Prime time average</th>";
            html += "<th>Previous Average</th><th>Previous Peak</th><th>Previous Prime time average</th></tr>";
            for(var i = 0; i < data.cols.length; i++){
                html += "<tr><td>" + data.cols[i].title + "</td><td>" + data.cols[i].avg + "</td><td>" +data.cols[i].max + "</td><td>" + data.cols[i].p_avg + "</td>";
                html += "<td>" + data.cols[i].pre_avg + "</td><td>" + data.cols[i].pre_max + "</td><td>" +data.cols[i].pre_p_avg + "</td></tr>";
            }
            html += "</table>";
        },
        dataType: "json"
    });

    return html;
}
