<html>
    <script type="text/javascript" src="lib/javascriptrrd.wlibs.js"></script>
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

    $.plot($("#flot-realtime"), dataset, options);
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

    $.plot($("#flot-realtime"), dataset, options);
    setTimeout(GetData, updateInterval);
});



</script>

    <!-- the above script replaces the rrdfFlotAsync,rrdFlot, rrdFlotSelection, rrdFile, binaryXHR and all the jquery libraries -->
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
    <title>Enemon: Energy Monitoring and Alerts</title>
  </head>

  <body>
    <br>
    <h1 id="title"><center>Enemon Energy Monitor </center></h1><br>

    <table id="infotable" border=1>
        <tr><td colspan="21"><b>Javascript needed for this page to work</b></td></tr>
    </table>


    <div id="flot-realtime" style="width:1000px;height:300px;margin:0 auto"></div>

    <div class="chart-container">
    	<div id="energyGraph" class="chart-placeholder"></div>
    </div>


    <script type="text/javascript">


      var endTime =  Number(+new Date()) - (8*60*60*1000) + (5*60*1000); //-8 Hours for time zone + 5 mintes empty space
      var startTime = endTime - 24*60*60*1000; //Show 24 hours by default
      // Remove the Javascript warning
      document.getElementById("infotable").deleteRow(0);

      var graph_opts={legend: { noColumns:4}};
      var ds_graph_opts={'ClientGlideIdle':{ color: "#ff8000", label: 'Idle Clinet Glideins', 
                                       lines: { show: true, fill: true, fillColor:"#ffff80"} },
                         'ClientGlideTotal':{ label: 'Total Client Glideins', color: "#00c0c0", 
                                  lines: { show: true, fill: true} },
                         'ClientInfoAge':{yaxis:2},
                         'StatusWait':{color: "#000000",yaxis:2}};
      var rrdflot_defaults={ num_cb_rows:9, use_element_buttons: true, 
                             multi_ds:false, multi_rra: true, 
                             use_rra: true, rra:1, 
                             //If multi_ds is off, don't need to include "-GAUGE" in element names
                             use_checked_DSs: true, checked_DSs: ["TotalWatts"], 
                             use_windows:true, window_min:startTime,window_max:endTime,
                             graph_width:"700px",graph_height:"300px", scale_width:"350px", scale_height:"200px",
                             timezone:"-8"};

       var fname="/home/data/homeEnergy.rrd";
       flot_obj=new rrdFlotAsync("energyGraph",fname,null,graph_opts,ds_graph_opts,rrdflot_defaults);

    </script>
  </body>
</html>


<!-- HTML -->


