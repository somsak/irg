// RRA object
function RRA(rra) {
    var id = rra.id;
    var name = rra.name;
    var timespan = rra.timespan;

    this.getRRAId = function() {
        return id;
    }

    this.getName = function() {
        return name;
    }

    this.getTimespan = function() {
        return timespan;
    }
}

RRA.getRRAById = function(id) {
    var rra;

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data: {
           c: "rras",
           a: "getById",
           id: id,
        },
        success: function(data){
            rra = new RRA(data);
        },
        dataType: "json"
    });

    return rra;
}

RRA.getRRAs = function(){
    var rras = new Array();

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data: {
           c: "rras",
           a: "get",
        },
        success: function(data){
            for(d in data){
                rras[data[d].id] = new RRA(data[d]);
            }
        },
        dataType: "json"
    });

    return rras;
}

RRA.HTML = {};
RRA.HTML.options = function(rras) {
    var html = '';
    for(rra in rras){
        html+= '<option value="' + rras[rra].getRRAId() + '">' + rras[rra].getName() + '</option>';
    }

    return html;
}
