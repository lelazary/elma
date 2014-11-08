#!/usr/bin/perl

use Time::HiRes qw( usleep ualarm gettimeofday tv_interval nanosleep clock_gettime clock_getres clock_nanosleep clock stat );
#
#print "Content-type: text/html\n\n";
#print "<HTML>Test</HTML>\n";


print "Server: MJPG-Streamer/0.2\n";
print "Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0\n";
print "Pragma: no-cache\n";
print "Expires: Mon, 3 Jan 2000 12:34:56 GMT\n";
print "Content-Type: multipart/x-mixed-replace;boundary=boundarydonotcross\n";
print "\n";

$| = 1;  # fflush stdout after print

foreach $x (1..2000)
{
  $f = $x%10;
  $f = $f + 1;
  print "--boundarydonotcross\n";
  $data = `cat /usr/local/BigBearHost/www/mjpg/test$f.jpg`;
  $cl = length($data);
  print "Content-Type: image/jpeg\n"; 
  #print "Content-Length: $cl\n"; 
  print "\n";
  print $data;
  #fflush stdout;
  usleep(100000);
  #sleep(1);
}

