#!/usr/bin/perl
use IO::Socket::INET;
use Data::Dumper;

use strict;

my $rootDir = "/usr/local/elma";
my $dataDir = "$rootDir/data";
open(FILE, "$dataDir/current.json") || die;
while(<FILE>)
{
	print;
}
close(FILE);
