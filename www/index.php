<html>
    <script type="text/javascript" src="lib/javascriptrrd.wlibs.js"></script>
    <!-- the above script replaces the rrdfFlotAsync,rrdFlot, rrdFlotSelection, rrdFile, binaryXHR and all the jquery libraries -->
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
    <title>Enemon: Energy Monitoring and Alerts</title>
  </head>

  <body>
    <h1 id="title">Current Energy</h1>

    <table id="infotable" border=1>
        <tr><td colspan="21"><b>Javascript needed for this page to work</b></td></tr>
    </table>


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


