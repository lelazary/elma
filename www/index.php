<H1>
Home Monitor
</H1>

<?php


function showGraph($data) {
	$graph_start = time() - (60*60*24);
	$graph_end = time();
	$rrdFile = "homeEnergy";

	$imgString = "cgi-bin/viewGraph.cgi?action=zoom&graph_start=$graph_start&graph_end=$graph_end&graph_height=150&graph_width=700&rrdFile=$rrdFile";
	echo "<a href=viewGraph.php?rrdFile=$rrdFile&data=$data>";
	echo "<img src=$imgString&data=$data></a><br>";
}


showGraph("total_kWh");
showGraph("total_kVARh");
showGraph("TotalWatts");

echo "<hr>";
showGraph("VoltsL1");
showGraph("AmpsL1");
showGraph("WattsL1");
showGraph("totalL1_kWh");

echo "<hr>";
showGraph("VoltsL2");
showGraph("AmpsL2");
showGraph("WattsL2");
showGraph("totalL2_kWh");

echo "<hr>";
showGraph("Pulse1");
showGraph("Pulse2");


?>
