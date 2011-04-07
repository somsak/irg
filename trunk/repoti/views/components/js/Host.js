// Host object
function Host(host){
    var id = host.id;
    var graphs = Graph.getGraphs(id);

    var hostname = host.hostname;
    var description = host.description;

    this.getGraphs = function(){
        return graphs;
    };

    this.getHostId = function() {
        return id;
    }

    this.getHostName = function() {
        return hostname;
    }

    this.getDescription = function() {
        return description;
    }
}

Host.getHosts = function(){
    var hosts = new Array();

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data: {
           c: "hosts",
           a: "get",
        },
        success: function(data){
            for(d in data){
                hosts[data[d].id] = new Host(data[d]);
            }
            hosts.splice(0, 1);
        },
        dataType: "json"
    });

    return hosts;
}

Host.getHostById = function(hostId) {
    var host;

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data: {
           c: "hosts",
           a: "getById",
           hostId: hostId,
        },
        success: function(data){
            host = new Host(data);
        },
        dataType: "json"
    });

    return host;
}

Host.HTML = {};
Host.HTML.ol = function(hosts){
    var html = "<div>";
    for(h in hosts){
        var host = hosts[h];
        html += "<h3>" + host.getDescription() + "</h3>";

        graphs = host.getGraphs();
        html += "<div>";
        html += "<ol>";
        for(g in graphs){
            var graph = graphs[g];
            html += "<li><b>host id: </b>" + graph.getHostId() + " ";
            html += "<b>graph id: </b>" + graph.getGraphId() + " ";
            html += "<b>title: </b>" + graph.getTitle() + "</li>";
        }
        html += "</ol>";
        html += "</li>";
        html += "</div>";
    }
    html += "</div>";

    return html;
}

Host.HTML.li = function(hosts){
    html = "";
    for(h in hosts) {
        html += "<li><input class='host' type='checkbox' value='" + hosts[h].getHostId() + "'/>";
        html += hosts[h].getDescription();
        html += "</li>";
    }

    return html;
}
