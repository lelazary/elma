<script type="text/javascript" src="lib/jquery-1.8.3.min.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/js/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript" src="lib/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="lib/flot/jquery.flot.time.js"></script>    
<script type="text/javascript" src="lib/flot/jshashtable-2.1.js"></script>    
<script type="text/javascript" src="lib/flot/jquery.numberformatter-1.2.3.min.js"></script>
<script type="text/javascript" src="lib/flot/jquery.flot.symbol.js"></script>
<script type="text/javascript" src="lib/flot/jquery.flot.axislabels.js"></script>
<script>
var totalWatts = [], wattsL1 = [], wattsL2 = [], total_kWh = [];
var dataset;
var totalPoints = 100;
var updateInterval = 1000;
var now = new Date().getTime();

var options = {
    series: {
        lines: {
            lineWidth: 1.2
        },
        bars: {
            align: "center",
            fillColor: { colors: [{ opacity: 1 }, { opacity: 1}] },
            barWidth: 500,
            lineWidth: 1
        }
    },
    xaxis: {
        mode: "time",
        tickSize: [60, "second"],
        tickFormatter: function (v, axis) {
            var date = new Date(v);

            if (date.getSeconds() % 20 == 0) {
                var hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
                var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
                var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();

                return hours + ":" + minutes + ":" + seconds;
            } else {
                return "";
            }
        },
        axisLabel: "Time",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 10
    },
    yaxes: [
        {
            min: 0,
            max: 10000,
            tickSize: 1000,
            tickFormatter: function (v, axis) {
                if (v % 10 == 0) {
                    return v/1000 + " kW";
                } else {
                    return "";
                }
            },
            axisLabel: "Watts",
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 12,
            axisLabelFontFamily: 'Verdana, Arial',
            axisLabelPadding: 6
        }, {
            min: 0,
            max: 500,
            tickFormatter: function (v, axis) {
                if (v % 10 == 0) {
                    return v + " kWh";
                } else {
                    return "";
                }
            },
            position: "right",
            axisLabel: "kWh",
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 12,
            axisLabelFontFamily: 'Verdana, Arial',
            axisLabelPadding: 6
        }
    ],
    legend: {
        noColumns: 2,
        position:"nw"
    },
    grid: {      
        backgroundColor: { colors: ["#ffffff", "#EDF5FF"] }
    }
};

function initData() {
    for (var i = 0; i < totalPoints; i++) {
        var temp = [now += updateInterval, 0];

        totalWatts.push(temp);
        wattsL1.push(temp);
        wattsL2.push(temp);
        total_kWh.push(temp);
    }
}

function GetData() {
    $.ajaxSetup({ cache: false });

    $.ajax({
        url: "getData.cgi",
        dataType: 'json',
        success: update,
        error: function () {
            setTimeout(GetData, updateInterval);
        }
    });
}

var temp;

function update(_data) {
    totalWatts.shift();
    wattsL1.shift();
    wattsL2.shift();
    total_kWh.shift();

    now += updateInterval

    temp = [now, _data.TotalWatts];
    totalWatts.push(temp);

    temp = [now, _data.WattsL1];
    wattsL1.push(temp);

    temp = [now, _data.WattsL2];
    wattsL2.push(temp);

    temp = [now, _data.total_kWh];
    total_kWh.push(temp);

    dataset = [
        { label: "TotalWatts: " + _data.TotalWatts + " W", data: totalWatts, lines: { fill: true, lineWidth: 1.2 }, color: "#00FF00" },
        { label: "WattsL1: " + _data.WattsL1 + " W", data: wattsL1, lines: { lineWidth: 1.2}, color: "#FF0000" },        
        { label: "WattsL2: " + _data.WattsL2 + " W", data: wattsL2, lines: { lineWidth: 1.2}, color: "#0000FF" },        
        { label: "Total kWh: " + _data.total_kWh + " kWh", data: total_kWh, lines: { lineWidth: 1.2}, color: "#FF00FF", yaxis: 2 }        
    ];

    $.plot($("#flot-placeholder1"), dataset, options);
    setTimeout(GetData, updateInterval);
}


$(document).ready(function () {
    initData();

    dataset = [        
        { label: "TotalWatts", data: totalWatts, lines:{fill:true, lineWidth:1.2}, color: "#00FF00" },
        { label: "WattsL1:", data: wattsL1, color: "#0044FF", bars: { show: true }, yaxis: 2 },
        { label: "WattsL2", data: wattsL2, lines: { lineWidth: 1.2}, color: "#FF0000" },
        { label: "TotalkWh", data: wattsL2, lines: { lineWidth: 1.2}, color: "#FF0000" }
    ];

    $.plot($("#flot-placeholder1"), dataset, options);
    setTimeout(GetData, updateInterval);
});



</script>
<!-- HTML -->
<div id="flot-placeholder1" style="width:550px;height:300px;margin:0 auto"></div>


