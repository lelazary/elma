#!/usr/bin/perl
use IO::Socket::INET;
use Data::Dumper;
use Proc::Daemon;
use Proc::PID::File;
use strict;
use RRDs;

my $rootDir = "/usr/local/enemon";
my $dataDir = "$rootDir/data";

my $daemon = Proc::Daemon->new(
		work_dir     => $rootDir,
		child_STDOUT => '+>>logs/debug.log',
		child_STDERR => '+>>logs/error.log'
		);

$daemon->Init();

if (Proc::PID::File->running(dir=>$rootDir)) {
	print "Already Running\n";
	exit;
}

my $DATA;
while(1)
{

	my $data1 = &readData("192.168.1.29", 50000, 100, "000300001743", "00");
	$DATA = unpack2Hash('A1:Header A2:meterType A1:firmware A12:address A8:total_kWh A8:total_kVARh A8:totalRev_kWh A8:totalL1_kWh A8:totalL2_kWh A8:totalL3_kWh A8:totalRevL1_kWh A8:totalRevL2_kWh A8:totalRevL3_kWh A8:totalRes_kWh A8:totalRevRes_kWh A4:VoltsL1 A4:VoltsL2 A4:VoltsL3 A5:AmpsL1 A5:AmpsL2 A5:AmpsL3 A7:WattsL1 A7:WattsL2 A7:WattsL3 A7:TotalWatts A4:CosL1 A4:CosL2 A4:CosL3 A7:VARL1 A7:VARL2 A7:VARL3 A7:VARL123 A4:Frequency A8:Pulse1 A8:Pulse2 A8:Pulse3 A1:PulseInState A1:CurrentDir A1:Output A1:DecimalPlaces A2:Reser A14:DateTime A2:Type A4:End A2:CRC16', $data1);
	#print Dumper($DATA), "\n";
	
	#Apply the decimal places to the numbers
	$$DATA{'VoltsL1'} /= 10;
	$$DATA{'AmpsL1'} /= 10; #(10**$$DATA{'DecimalPlaces'});
	$$DATA{'totalL1_kWh'} /= (10**$$DATA{'DecimalPlaces'});
	$$DATA{'VoltsL2'} /= 10;
	$$DATA{'AmpsL2'} /= 10;
	$$DATA{'totalL2_kWh'} /= (10**$$DATA{'DecimalPlaces'});

	$$DATA{'total_kWh'} /= (10**$$DATA{'DecimalPlaces'});
	$$DATA{'total_kVARh'} /= (10**$$DATA{'DecimalPlaces'});

	#Only update if we got good values
	#TOCO: check for CRC
	if (unpack("H*", $$DATA{End}) == "210d0a03")
	{
		createRRD("$dataDir/homeEnergy.rrd") if (!(-e "$dataDir/homeEnergy.rrd"));
		updateValues("$dataDir/homeEnergy.rrd", $DATA);

		#Save the data into a json file
		my $json = "{". join(", ", map { "\"$_\": " . ($$DATA{$_} + 0)  } keys %$DATA). "}";
		open(JSON, ">$dataDir/current.json");
		print JSON $json;
		close(JSON);
	}
}

sub readData () {
	my ($peerAddr, $peerPort, $peerTimeout, $meterID, $type) = @_;
	my $rxData = '';

	my $sock = IO::Socket::INET->new (
		PeerAddr => $peerAddr,
		PeerPort => $peerPort,
		Proto => 'tcp',
		Timeout => $peerTimeout,
		Type => SOCK_STREAM,
	) or die "ERROR in Socket Creation : $!\n";

	my $txData = '/?' . $meterID . $type . "!\r\n";
	$sock->send($txData);
	sleep(1); #Wait for the iSerial to respond
	$sock->recv($rxData,1024);

	close($sock);

	return $rxData;
}


sub unpack2Hash {
	my ($template, $source) = @_;

	my ($i, @field, @format) = 0;
	push @{ (\@format, \@field)[$i++ & 1] }, $_
	for map /^([^:]+):(.*)/, split " ", $template;

	local $" = " ";
	return { map +(shift(@field) => $_), unpack "@format", $source };
}

sub createRRD {
	my ($filename) = @_;
	RRDs::create ($filename,
		"--step", "60", 
		"DS:VoltsL1:GAUGE:120:0:100000 ",  #60*24*7
		"DS:AmpsL1:GAUGE:120:0:100000 ",  #60*24*7
		"DS:WattsL1:GAUGE:120:0:100000 ",  #60*24*7
		"DS:VARL1:GAUGE:120:0:100000 ",  #60*24*7
		"DS:totalL1_kWh:GAUGE:120:0:100000 ",  #60*24*7
		"DS:VoltsL2:GAUGE:120:0:100000 ",  #60*24*7
		"DS:AmpsL2:GAUGE:120:0:100000 ",  #60*24*7
		"DS:WattsL2:GAUGE:120:0:100000 ",  #60*24*7
		"DS:VARL2:GAUGE:120:0:100000 ",  #60*24*7
		"DS:totalL2_kWh:GAUGE:120:0:100000 ",  #60*24*7

		"DS:TotalWatts:GAUGE:120:0:100000 ",  #60*24*7
		"DS:VARL123:GAUGE:120:0:100000 ",  #60*24*7
		"DS:total_kWh:GAUGE:120:0:100000 ",  #60*24*7
		"DS:total_kVARh:GAUGE:120:0:100000 ",  #60*24*7
		"DS:Pulse1:COUNTER:120:0:100000 ",  #60*24*7
		"DS:Pulse2:COUNTER:120:0:100000 ",  #60*24*7

		"RRA:AVERAGE:0.5:1:10080 ",  #60*24*7 evey minute
		"RRA:AVERAGE:0.5:5:8640 ",  #12*24*31 Evey 5 min for 31 days
		"RRA:AVERAGE:0.5:60:17280 ",  #24*360*2 1 Hour For 2 years
		"RRA:HWPREDICT:1440:0.1:0.0035:288:3 ",
		"RRA:SEASONAL:288:0.1:2 ",
		"RRA:DEVPREDICT:1440:5 ",
		"RRA:DEVSEASONAL:288:0.1:2 ",
		"RRA:FAILURES:288:7:9:5 ");
	my $ERR=RRDs::error;
	die "ERROR while creating $filename: $ERR\n" if $ERR;
}
sub updateValues {
	my ($filename, $values) = @_;
	my @keys = ('VoltsL1', 'AmpsL1','WattsL1', 'VARL1', 'totalL1_kWh', 'VoltsL2', 'AmpsL2',
		'WattsL2', 'VARL2', 'totalL2_kWh', 'TotalWatts', 'VARL123', 'total_kWh', 'total_kVARh',
		'Pulse1', 'Pulse2');

	my $template =  join(":", @keys), "\n";
	my $data = join(":", map { $$values{$_} } @keys);
	RRDs::update ($filename, "--template", $template, "N:$data");

	my $ERR=RRDs::error;
	die "ERROR while updating $filename: $ERR\n" if $ERR;
}


