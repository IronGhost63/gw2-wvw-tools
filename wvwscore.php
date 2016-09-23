<?php
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$server_name_raw = json_decode(file_get_contents("http://api.guildwars2.com/v1/world_names", false, stream_context_create($arrContextOptions)));
$server_name = array();

foreach($server_name_raw as $single){
	$server_name[$single->id] = $single->name;
}

$matches = array(
	'1-1', '1-2', '1-3', '1-4',
	'2-1', '2-2', '2-3', '2-4', '2-5'
);

$wvwscore_base = "http://api.guildwars2.com/v2/wvw/matches/";

foreach($matches as $match){
	$score = json_decode(file_get_contents($wvwscore_base.$match, false, stream_context_create($arrContextOptions)));
	
	echo "---------------------------------------------------------------------\n";
	echo " Match ID: ".$match."\n";

	$team['red']['host'] = $score->worlds->red;
	$team['blue']['host'] = $score->worlds->blue;
	$team['green']['host'] = $score->worlds->green;

	$team = array(
		'red' => array(
			'host' => $score->worlds->red,
			'guests' => array_diff($score->all_worlds->red, array($score->worlds->red)),
			'scores' => $score->scores->red,
			'kills' => $score->kills->red,
			'deaths' => $score->deaths->red

		),
		'blue' => array(
			'host' => $score->worlds->blue,
			'guests' => array_diff($score->all_worlds->blue, array($score->worlds->blue)),
			'scores' => $score->scores->blue,
			'kills' => $score->kills->blue,
			'deaths' => $score->deaths->blue
		),
		'green' => array(
			'host' => $score->worlds->green,
			'guests' => array_diff($score->all_worlds->green, array($score->worlds->green)),
			'scores' => $score->scores->green,
			'kills' => $score->kills->green,
			'deaths' => $score->deaths->green
		)
	);

	$color = array('red', 'blue', 'green');
	foreach($color as $single){
		if(count($team[$single]['guests']) > 0){
			foreach($team[$single]['guests'] as $team_guest){
				$guest[$single][] = $server_name[$team_guest];
			}
			$guest_name[$single] = "(".implode(", ", $guest[$single]).")";
		}
		unset($guest);
	}

	echo "   Red Team: ". $server_name[$team['red']['host']] ." ".$guest_name['red']."\n";
	echo "    - Score: ". number_format($team['red']['scores'], "0", "", ",") ."\n";
	echo "    - Kills: ". number_format($team['red']['kills'], "0", "", ",") ."\n";
	echo "    - Deaths: ". number_format($team['red']['deaths'], "0", "", ",") ."\n";
	echo "    - KDR: ". number_format(($team['red']['kills']/$team['red']['deaths']), 2, '.', '') ."\n";

	echo "   Blue Team: ". $server_name[$team['blue']['host']] ." ".$guest_name['blue']."\n";
	echo "    - Score: ". number_format($team['blue']['scores'], "0", "", ",") ."\n";
	echo "    - Kills: ". number_format($team['blue']['kills'], "0", "", ",") ."\n";
	echo "    - Deaths: ". number_format($team['blue']['deaths'], "0", "", ",") ."\n";
	echo "    - KDR: ". number_format(($team['blue']['kills']/$team['blue']['deaths']), 2, '.', '') ."\n";

	echo "   Green Team: ". $server_name[$team['green']['host']] ." ".$guest_name['green']."\n";
	echo "    - Score: ". number_format($team['green']['scores'], "0", "", ",") ."\n";
	echo "    - Kills: ". number_format($team['green']['kills'], "0", "", ",") ."\n";
	echo "    - Deaths: ". number_format($team['green']['deaths'], "0", "", ",") ."\n";
	echo "    - KDR: ". number_format(($team['green']['kills']/$team['green']['deaths']), 2, '.', '') ."\n";

	unset($guest_name);
}

?>