#!/usr/bin/perl
use IO::Socket::INET;
use Data::Dumper;

use strict;


my $data1 = &readData("192.168.1.29", 50000, 100, "000300001743", "00");
my $DATA = unpack2Hash('A1:Header A2:meterType A1:firmware A12:address A8:total_kWh A8:total_kVARh A8:totalRev_kWh A8:totalL1_kWh A8:totalL2_kWh A8:totalL3_kWh A8:totalRevL1_kWh A8:totalRevL2_kWh A8:totalRevL3_kWh A8:totalRes_kWh A8:totalRevRes_kWh A4:VoltsL1 A4:VoltsL2 A4:VoltsL3 A5:AmpsL1 A5:AmpsL2 A5:AmpsL3 A7:WattsL1 A7:WattsL2 A7:WattsL3 A7:TotalWatts A4:CosL1 A4:CosL2 A4:CosL3 A7:VARL1 A7:VARL2 A7:VARL3 A7:VARL123 A4:Frequency A8:Pulse1 A8:Pulse2 A8:Pulse3 A1:PulseInState A1:CurrentDir A1:Output A1:DecimalPlaces A2:Reser A14:DateTime A2:Type A4:End A2:CRC16', $data1);
#print Dumper($DATA), "\n";


my @keys = ('VoltsL1', 'AmpsL1','WattsL1', 'totalL1_kWh', 'VoltsL2', 'AmpsL2',
		'WattsL2', 'totalL2_kWh', 'TotalWatts', 'total_kWh', 'total_kVARh',
		'Pulse1', 'Pulse2');

#Apply the decimal places to the numbers
$$DATA{'VoltsL1'} /= 10;
$$DATA{'AmpsL1'} /= 10; #(10**$$DATA{'DecimalPlaces'});
$$DATA{'totalL1_kWh'} /= (10**$$DATA{'DecimalPlaces'});
$$DATA{'VoltsL2'} /= 10;
$$DATA{'AmpsL2'} /= 10;
$$DATA{'totalL2_kWh'} /= (10**$$DATA{'DecimalPlaces'});

$$DATA{'total_kWh'} /= (10**$$DATA{'DecimalPlaces'});
$$DATA{'total_kVARh'} /= (10**$$DATA{'DecimalPlaces'});

$$DATA{TotalWatts} = int($$DATA{TotalWatts});


my @keys = ('VoltsL1', 'AmpsL1','WattsL1', 'totalL1_kWh', 'VoltsL2', 'AmpsL2',
		'WattsL2', 'totalL2_kWh', 'TotalWatts', 'total_kWh', 'total_kVARh',
		'Pulse1', 'Pulse2');

my $json = "{". join(", ", map { "\"$_\": " . int($$DATA{$_}) } @keys). "}";
print $json, "\n";
#print "{\"TotalWatts\": 1000, \"WattsL1\": 20 , \"WattsL2\":30}\n";

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

