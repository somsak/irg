Utils = {};
Utils.convertToTimestamp = function(datetime) {
    var timestamp;

    $.ajax({
        type: "GET",
        async: false,
        url: "/cacti/plugins/repoti/repoti.php",
        data:{
            c: "utils",
            a: "timestamp",
            datetime: datetime,
        },
        success: function(data){
            timestamp = data;
        },
        dataType: "json",
    });

    return timestamp;
}
