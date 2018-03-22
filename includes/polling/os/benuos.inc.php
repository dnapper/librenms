<?php

preg_match('/BenuOS\, (.*)\n.Product\:(.*)\n.*\n.*\n Chassis Type \:(.*)/', $device['sysDescr'], $matches);

$version  = $matches['1'];
$features = $matches['2'];
$hardware = $matches['3'];

$serial = snmp_get($device, 'benuChassisId.0', '-Ovqs', 'BENU-CHASSIS-MIB');

$oids = array(
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.6.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.7.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.8.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.9.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.10.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.11.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.12.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.13.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.14.1.1',
    '.1.3.6.1.4.1.39406.2.1.4.3.2.1.15.1.1',
);

$radiusacct_data = snmp_get_multi_oid($device, $oids);
list ($requestrcvd, $requestsent, $startrequestrcvd, $stoprequestrcvd, $interimupdatercvd, $startrequestsent, $stoprequestsent, $interimupdatesent, $responsercvd, $responsesent) = array_values($radiusacct_data);

$rrd_def = RrdDefinition::make()
    ->addDataset('RequestRcvd', 'GAUGE', 0, 10000000);
    ->addDataset('RequestSent', 'GAUGE', 0, 10000000);

$fields = array(
    'RequestRcvd'  => $requestrcvd,
    'RequestSent'  => $requestsent,
);

$tags = compact('rrd_def');
data_update($device, 'benuos_RadiusProxy', $tags, $fields);


$graphs['benuos_RadiusProxy'] = true;