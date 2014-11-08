<H1>
Home Monitor
</H1>

<?php

$graph_start = time() - (60*60*24);
$graph_end = time();
$rrdFile = "homeEnergy";

$imgString = "cgi-bin/viewGraph.cgi?action=zoom&graph_start=$graph_start&graph_end=$graph_end&graph_height=150&graph_width=700&rrdFile=$rrdFile";

echo "<img src=$imgString&data=total_kWh><br>";
echo "<img src=$imgString&data=total_kVARh><br>";
echo "<img src=$imgString&data=TotalWatts><br>";
echo "<hr>";
echo "<img src=$imgString&data=VoltsL1><br>";
echo "<img src=$imgString&data=AmpsL1><br>";
echo "<img src=$imgString&data=WattsL1><br>";
echo "<img src=$imgString&data=totalL1_kWh><br>";
echo "<hr>";
echo "<img src=$imgString&data=VoltsL2><br>";
echo "<img src=$imgString&data=AmpsL2><br>";
echo "<img src=$imgString&data=WattsL2><br>";
echo "<img src=$imgString&data=totalL2_kWh><br>";
echo "<hr>";
echo "<img src=$imgString&data=Pulse1><br>";
echo "<img src=$imgString&data=Pulse2><br>";

?>
