<?php

include('khmerLegacy2Unicode.php');

$string = 'mnusSTaMgGs;ekItmkmanesrIPaBnigsmPaBkñúgEpñkesckþIéføfñÚrnigsiT§i. mnusSmanvicarNBaØaN nigstism,CBaØ³Cab;BIkMeNItehIyKb,IRbRBwtþcMeBaHKñaeTAvíjeTAmkkñúgsµartIPatrPaBCabgb¥Ún.';

$db = new SQLite3('conversiondata.db');

$results = $db->query('SELECT * FROM conversion WHERE id=387');
$row = $results->fetchArray();
$charmap = json_decode($row['toUnicode'], true);

echo transcode($string, $charmap);

?>
