var report = new Report();
var previewGraphs = new Array();
var templates = Report.getReportTemplates();
var dateFormat = 'yy/mm/dd';

var all_graph = new Array();
var rras = RRA.getRRAs();
var hosts = Host.getHosts();

make_report = {};
make_report.init = function() {
    $("#report-rra-type-id").append(RRA.HTML.options(rras));
    $("#template-id").append(Report.HTML.template_options(templates));

    $("#report-rra-type-id").bind("change", function(e){
            make_report.rraTypeOnchange($(this).val());
        });
    $("#report-end-date").bind("change", function(e){
            make_report.endDateOnchange($(this).val());
        });
    $("#report-end-date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: dateFormat,
            buttonImage: '/cacti/plugins/repoti/images/ui/calendar.gif',
            numberOfMonths: 2,
            maxDate: 0,
        });
    $("#report-end-time").bind("change", function(e){
            make_report.endTimeOnchange($(this).val());
        });
    $("#report-begin-prime-time").bind("change", function(e){
            make_report.beginPrimeTimeOnchange($(this).val());
        });
    $("#report-end-prime-time").bind("change", function(e){
            make_report.endPrimeTimeOnchange($(this).val());
        });
    $("#report-update").bind("click", function(e){
    		e.preventDefault();
        	make_report.update();
    	});    
    $("#collapse-expend-host-graph").bind("click", function(e){
            $(".graph-list").toggle();
    	});
    
    make_report.updateConf();

    $("#graph-select").append(Graph.HTML.li_select(hosts));
    $("#graph-select").sortable({
            stop: function() {
            }
        });
    $(".graph-list").sortable({
            stop: function() {
            }
        });

    $("#graph-select .host-desc").bind("click", function() {
            $(this).find("~ ul").toggle();
        });

    $("#save-as-template").bind("click", function() {
            report.setName($("#template-name").val());
            make_report.update();
            Report.saveAsTemplate(report);

            $("#template-id *").remove()

            templates = Report.getReportTemplates();
            $("#template-id").append(Report.HTML.template_options(templates));
        });

    $("#load-template").bind("click", function() {
            make_report.loadTemplate();
        });

    $("#delete-template").bind("click", function() {
	    	if(confirm("ลบไหม?")) {
	            Report.deleteReportTemplate($("#template-id").val());
	
	            $("#template-id *").remove()
	            templates = Report.getReportTemplates();
	            $("#template-id").append(Report.HTML.template_options(templates));
	    	}
        });
}

make_report.rraTypeOnchange = function(rraTypeId) {
	report.setRRAType(rras[rraTypeId]);
	
	var rra = report.getRRAType();

	$("#conf-rra-type-id").html(rra.getName());
    if(rra.getTimespan() < 86400){
        $("#report-end-time").attr("disabled", "");
    } else {
        $("#report-end-time").attr("disabled", "disabled").val("00:00");
        $("#conf-end-time").html("00:00");
    }
}

make_report.endDateOnchange = function(date) {
    report.setEndDate(date);
    $("#conf-end-date").html(report.getEndDate());
}

make_report.endTimeOnchange = function(time) {
    report.setEndTime(time);
    $("#conf-end-time").html(report.getEndTime());
}

make_report.beginPrimeTimeOnchange = function(time) {
    report.setBeginPrimeTime(time);
    $("#conf-begin-prime-time").html(report.getBeginPrimeTime());
}

make_report.endPrimeTimeOnchange = function(time) {
    report.setEndPrimeTime(time);
    $("#conf-end-prime-time").html(report.getEndPrimeTime());
}

make_report.updateConf = function() {
    make_report.rraTypeOnchange($("#report-rra-type-id").val());
    make_report.endDateOnchange($("#report-end-date").val());
    make_report.endTimeOnchange($("#report-end-time").val());
    make_report.beginPrimeTimeOnchange($("#report-begin-prime-time").val());
    make_report.endPrimeTimeOnchange($("#report-end-prime-time").val());
}

make_report.update = function() {
    var checked = $(".graph-list :checked");
    
    $.each(checked, function(i, e) {
    	var id = $(e).val();
    	for(h in hosts) {
    		hGraphs = hosts[h].getGraphs();
    		
    		for(g in hGraphs) {
    			if(hGraphs[g].getGraphId() == id) {
    				previewGraphs.push(hGraphs[g]);
    			}
    		}
    	}
    });

    report.setGraphs(previewGraphs);
    $("#preview").html("<div>" + Report.HTML.li_preview(report) + "</div>");

    previewGraphs = new Array();
}

make_report.loadTemplate = function() {
    var templateId = $("#template-id").val();
    var template = templates[templateId];
    var templateRRA = template.getRRAType();
    var templateGraphs = template.getGraphs();
    
	$.each($(".graph-list input[type=checkbox]:checked"), function(i, e) {
		var id = $(e).attr("checked", "");
	});    
    
    for(g in templateGraphs) {
    	if(templateGraphs[g] != undefined) { 
    		$(".graph-list input[type=checkbox]").each(function() {
    			if($(this).val() == templateGraphs[g].getGraphId()) {
    				$(this).attr("checked", "checked");
    			}
    		})
    	} else {
    		alert("Graph ID=" + g + " is missing.");
    	}
    }

    $("#report-rra-type-id").val(templateRRA.getRRAId());
    make_report.rraTypeOnchange(templateRRA.getRRAId());
    $("#report-end-date").val(template.getEndDate());
    $("#report-end-time").val(template.getEndTime());
    $("#report-begin-prime-time").val(template.getBeginPrimeTime());
    $("#report-end-prime-time").val(template.getEndPrimeTime());

    make_report.update();
}

make_report.toggle = function() {
    $(".hide-item").toggle(1, function() {
        if($("#repoti-right").css("max-width") == "700px") {
            $("#repoti-left").css({
                'max-width': '0',
                'min-width': '0'
                });
            $("#repoti-right").css({
                'max-width': '100%',
                'min-width': '100%',
                'position': 'absolute',
                'left': '0', 'top': '0'
                });
        } else {
            $("#repoti-left").css({
                'max-width': '320px',
                'min-width': '320px'
                });
            $("#repoti-right").css({
                'max-width': '700px',
                'min-width': '700px',
                'position': 'absolute',
                'left': '335',
                'top': '0'
                });
        }
    });
}

$(document).ready(function() {
    make_report.init();
});
