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
            graphStart : report.getGraphBeginTimestamp(),
            graphEnd : report.getGraphEndTimestamp(),
            beginPrime : report.getBeginPrimeTime(),
            endPrime : report.getEndPrimeTime(),
        },
        success: function(data){
            var td1 = "";
            var td2 = "";

            for(var i = 0; i < data.cols.length; i++){
                // Data table here
                html = "";
                html += "<table class='repoti graph-stat'>";
                html += "<tr><th>Value:</div></th><td>" + data.cols[i].title + "</td><br></tr>";
                html += "<tr><th>AVERAGE:</th><td>" + data.cols[i].avg + "</td></tr>";
                html += "<tr><th>PEAK:</th><td>" + data.cols[i].max + "</td></tr>";
                html += "<tr><th>PRIME TIME AVERAGE:</th><td>" + data.cols[i].p_avg + "</td></tr>";
                html += "</table>";

                if( i % 2 == 0) {
                    td1 += html;
                } else {
                    td2 += html;
                }
            }

            html = "<table><tr><td>" + td1 + "</td><td>" + td2 + "</td></tr></table>";
        },
        dataType: "json",
    });

    return html;
}
