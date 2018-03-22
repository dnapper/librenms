<?php

use LibreNMS\RRD\RrdDefinition;

preg_match('/BenuOS\, (.*)\n.Product\:(.*)\n.*\n.*\n Chassis Type \:(.*)/', $device['sysDescr'], $matches);

$version  = $matches['1'];
$features = $matches['2'];
$hardware = $matches['3'];

$serial = snmp_get($device, 'benuChassisId.0', '-Ovqs', 'BENU-CHASSIS-MIB');

$radiusproxyoids = array(
        'bRadiusProxyAcctRequestRcvd.1.1',
        'bRadiusProxyAcctRequestSent.1.1',
        'bRadiusProxyAcctStartRequestRcvd.1.1',
        'bRadiusProxyAcctStopRequestRcvd.1.1',
        'bRadiusProxyAcctInterimUpdateRcvd.1.1',
        'bRadiusProxyAcctStartRequestSent.1.1',
        'bRadiusProxyAcctStopRequestSent.1.1',
        'bRadiusProxyAcctInterimUpdateSent.2.1',
        'bRadiusProxyAcctResponseRcvd.1.1',
        'bRadiusProxyAcctResponseSent.1.1',
);

$radiusacct_data = snmp_get_multi_oid($device, $radiusproxyoids, '-OUQs', 'BENU-RADIUS-MIB');

foreach ($radiusacct_data as $key=>$value) {
  $name = preg_replace('~[1-9\s:\s.]+$~', '', $key);
  $graphname = preg_replace('/^bRadiusProxyAcct/', "benuos_radius_", $name);
  d_echo("$graphname $name $value\n");
  $rrd_name = array($name);
  $rrd_def = RrdDefinition::make()->addDataset('radius', 'GAUGE', 0, 1000000);

  $fields = array(
      'radius'  => $value,
  );

  $tags = compact('rrd_name', 'rrd_def');
  data_update($device, $name, $tags, $fields);
  
  $graphs[$graphname] = true;
}