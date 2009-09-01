// Host object
function Host(host){
    var id = host.id;
    var graphs = Graph.getGraphs(id);

    var hostname = host.hostname;
    var description = host.description;

    this.getGraphs = function(){
        return graphs;
    };

    this.getHostId = function(){
        return id;
    }

    this.getHostName = function(){
        return hostname;
    }

    this.getDescription = function(){
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
    html = "<div>";
    for(h in hosts){
        html += "<li class='host-li'>";
        html += "<h3>" + hosts[h].getDescription() + "</h3>";

        graphs = hosts[h].getGraphs();
        html += "<div>";
        html += "<ol>";
        for(g in graphs){
            html += "<li><b>host id: </b>" + graphs[g].getHostId() + " ";
            html += "<b>graph id: </b>" + graphs[g].getGraphId() + " ";
            html += "<b>graph url: </b>" + graphs[g].getImageUrl() + " ";
            html += "<b>title: </b>" + graphs[g].getTitle() + "</li>";
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
