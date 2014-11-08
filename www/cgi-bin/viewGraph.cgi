#!/usr/bin/perl

#use RRD::CGI::Image;
use RRDs;
use CGI;
use IO::Handle;


$rrdFile = CGI::param('rrdFile');
$data = CGI::param('data');
$rrdName = $data;
$debug = 0;

print "Content-type: text/html\n\n" if ($debug);
@graphParams = ();

@colors = ("ff0000", "00ff00", "0000ff", "ffff00", "ff00ff", "00ffff", "000000", "3D2B1F", "333399", "00DDDD", "8A2BE2", "79443B", "0095B6", "B5A642", "66FF00", "FF007F", "08E8DE", "FF55A3", "004225", "964B00", "800020", "702963" );

$printCmd = "GPRINT";

$rrdFile = "/home/elazary/house/meter/enemon/data/${rrdFile}.rrd";
push(@graphParams, "DEF:obs=$rrdFile:$data:AVERAGE");

push(@graphParams, "LINE2:obs#00ff00:$rrdName\\n" );
push(@graphParams, "$printCmd:obs:LAST:Current\\:%0.2lf");
push(@graphParams, "$printCmd:obs:AVERAGE:Avg\\:%0.2lf");
push(@graphParams, "$printCmd:obs:MIN:Min\\:%0.2lf");
push(@graphParams, "$printCmd:obs:MAX:Max\\:%0.2lf");

if ($debug)
{
  print join("<BR>", @graphParams);
  exit(0);
}
STDOUT->autoflush(1);
$graphOutput = "-";
if ($rawStats)
{
  print CGI::header( 'text/html' );
  $graphOutput = "/dev/null";
} else {
  print CGI::header( 'image/png' );
}

($results, $xsize, $ysize) = RRDs::graph(    $graphOutput,
                "--imgformat" => "PNG",
                "--start" => CGI::param('graph_start'),
                "--end" => CGI::param('graph_end'),
                "--title" => "Showing: $servers",
                "--base" => "1000",
                "--height" => "120",
                "--width" => "700",
                "--alt-autoscale-max",
                "--lower-limit" => "0",
                "--slope-mode",
                @graphParams,
        );

my $ERR=RRDs::error;
 die "Cannot generate graph: $ERR\n" if $ERR;

sub dateToStr 
{

  local($time) = @_;

  ($sec,$min,$hour,$mday,$mon,$year,$wday,
   $yday,$isdst)=localtime($time);
  $dateStr = sprintf("%4d-%02d-%02d %02d:%02d:%02d",
         $year+1900,$mon+1,$mday,$hour,$min,$sec);
  return $dateStr;
}

