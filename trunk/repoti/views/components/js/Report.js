function Report(report) {
    var reportGraphs;
    var name;
    var rraType;
    var templateId;

    var beginDate;
    var beginDateTimestamp;
    var beginTime = "00:00";

    var endDate;
    var endDateTimestamp;
    var endTime = "00:00";

    var beginPrime;
    var endPrime;

    this.getName = function() {
        return name;
    }

    this.setName = function(n) {
        name = n;
    }

    this.getBeginDate = function() {
        return beginDate;
    }

    this.getEndDate = function() {
        return endDate;
    }

    this.setEndDate = function(date) {
        endDate = date;
        endDateTimestamp = Utils.convertToTimestamp(endDate + " " + endTime);
        beginDateTimestamp = endDateTimestamp - rraType.getTimespan();
        beginDate = $.datepicker.formatDate('yy/mm/dd', $.datepicker.parseDate("@", beginDateTimestamp + "000"));
    }

    this.getEndTime = function() {
        return endTime;
    }

    this.setEndTime = function(time) {
        endTime = time;
        endDateTimestamp = Utils.convertToTimestamp(endDate + " " + endTime);
        beginDateTimestamp = endDateTimestamp - rraType.getTimespan();
    }

    this.getBeginPrimeTime = function() {
        return beginPrime;
    }

    this.setBeginPrimeTime = function(time) {
        beginPrime = time;
    }

    this.getEndPrimeTime = function() {
        return endPrime;
    }

    this.setEndPrimeTime = function(time) {
        endPrime = time;
    }

    this.getGraphBeginTimestamp = function() {
        return beginDateTimestamp;
    }

    this.getGraphEndTimestamp = function() {
        return endDateTimestamp;
    }

    this.getRRAType = function() {
        return rraType;
    }

    this.setRRAType = function(rra) {
        rraType = rra;
        beginDateTimestamp = endDateTimestamp - rraType.getTimespan();
    }

    this.setGraphs = function(graphs) {
        reportGraphs = graphs;
    }

    this.getGraphs = function() {
        return reportGraphs;
    }

    this.getTemplateId = function() {
        return templateId;
    }

    this.setTemplateId = function(id) {
        templateId = id;
    }
}

Report.saveAsTemplate = function(report) {
    var rraType = report.getRRAType();
    var graphIds = new Array();
    var graphs = report.getGraphs();

    for(g in graphs) {
        graphIds.push(graphs[g].getGraphId());
    }

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "reports",
            a: "saveAsTemplate",
            templateName: report.getName(),
            graphIds: graphIds.toString(),
            rraTypeId: rraType.getRRAId(),
            beginPrime: report.getBeginPrimeTime(),
            endPrime: report.getEndPrimeTime(),
        },
        success: function(data){

        },
        dataType: "json",
        });
}

Report.getReportTemplates = function() {
    var templates = new Array();

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "reports",
            a: "get",
        },
        success: function(data){
            for(d in data){
                tmp = data[d];
                var rra = RRA.getRRAById(tmp.rratype_id);
                var graphIds = tmp.graph_ids.split(',');
                var graphs = new Array();

                for(g in graphIds) {
                    var id = graphIds[g];
                    if(id != '') {
                        graph = Graph.getGraphById(id);
                        graphs.push(graph);
                    }
                }

                template = new Report();
                template.setTemplateId(tmp.id);
                template.setName(tmp.template_name);
                template.setRRAType(rra);
                template.setBeginPrimeTime(tmp.begin_prime);
                template.setEndPrimeTime(tmp.end_prime);
                template.setGraphs(graphs);

                templates[tmp.id] = template;
            }
        },
        dataType: "json",
    });

    return templates;
}

Report.deleteReportTemplate = function(id) {
    $.ajax({
        type: "GET",
        async: true,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "reports",
            a: "deleteTemplate",
            id: id,
        },
        success: function(data){

        },
        dataType: "json",
    });
}

Report.HTML = {};
Report.HTML.li_preview = function(report) {
	graphs = report.getGraphs();
	
    return Graph.HTML.getGraphStatsTables(report);
}

Report.HTML.template_options = function(templates) {
    var html = "";

    for(template in templates){
        html+= '<option value="' + templates[template].getTemplateId() + '">' + templates[template].getName() + '</option>';
    }

    return html;
}
