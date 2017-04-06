<?php

/**
 * Get commandline args
 */
$opt_short = "";
$opt_long = array(
	"zone:",
	"match:",
	"template:"
);

$options = getopt($opt_short, $opt_long);

/**
 * Get Server Name
 */
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$server_name_raw = json_decode(file_get_contents("https://api.guildwars2.com/v2/worlds?ids=all", false, stream_context_create($arrContextOptions)));
$server_name = array();

foreach($server_name_raw as $single){
	$server_name[$single->id] = $single->name;
}

/**
 * Zone selection
 */

$matches_zone = array(
	'na' => array('1-1', '1-2', '1-3', '1-4'),
	'eu' => array('2-1', '2-2', '2-3', '2-4', '2-5')
);

if( in_array( $options['zone'], array('na', 'eu') ) ) {
	$matches = $matches_zone[$options['zone']];
}else{
	$matches = array_merge( $matches_zone['na'], $matches_zone['eu'] );
}

/**
 * Matches selection
 * Match option will override Zone option
 */

if(isset($options['match'])){
	$matches = explode(",", $options['match']);
}

/**
 * Load Template
 */

$use_template = false;
if(isset($options['template'])){
	$use_template = true;
	$template = explode("<!-- START TIER LOOP -->", file_get_contents($options['template']));

	$template_head = $template[0];

	$template = explode("<!-- END TIER LOOP -->", $template[1]);

	$template_body = $template[0];
	$template_footer = $template[1];
}

$wvwscore_base = "https://api.guildwars2.com/v2/wvw/matches/";

if($use_template){
	echo $template_head;
}

foreach($matches as $match){
	$score = json_decode(file_get_contents($wvwscore_base.$match, false, stream_context_create($arrContextOptions)));
	if(!$use_template){
		echo "---------------------------------------------------------------------\n";
		echo " Match ID: ".$match."\n";
	}

	$team['red']['host'] = $score->worlds->red;
	$team['blue']['host'] = $score->worlds->blue;
	$team['green']['host'] = $score->worlds->green;

	$team = array(
		'red' => array(
			'host' => $score->worlds->red,
			'guests' => array_diff($score->all_worlds->red, array($score->worlds->red)),
			'scores' => $score->scores->red,
			'current_skirmish_scores' => end($score->skirmishes)->scores->red,
			'kills' => $score->kills->red,
			'deaths' => $score->deaths->red,
			'v_scores' => $score->victory_points->red

		),
		'blue' => array(
			'host' => $score->worlds->blue,
			'guests' => array_diff($score->all_worlds->blue, array($score->worlds->blue)),
			'scores' => $score->scores->blue,
			'current_skirmish_scores' => end($score->skirmishes)->scores->blue,
			'kills' => $score->kills->blue,
			'deaths' => $score->deaths->blue,
			'v_scores' => $score->victory_points->blue
		),
		'green' => array(
			'host' => $score->worlds->green,
			'guests' => array_diff($score->all_worlds->green, array($score->worlds->green)),
			'scores' => $score->scores->green,
			'current_skirmish_scores' => end($score->skirmishes)->scores->green,
			'kills' => $score->kills->green,
			'deaths' => $score->deaths->green,
			'v_scores' => $score->victory_points->green
		)
	);

	$guest_name = array(
		'red' => '',
		'green' => '',
		'blue' => ''
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
	
	if($use_template){
		$tier_num = explode("-", $match);
		$red_team = $server_name[$team['red']['host']] ." ".$guest_name['red'];
		$blue_team = $server_name[$team['blue']['host']] ." ".$guest_name['blue'];
		$green_team = $server_name[$team['green']['host']] ." ".$guest_name['green'];
		$output = $template_body;
		$output = str_replace("<!-- TXT TIER_NUM -->", $tier_num[1],$output);
		$output = str_replace("<!-- TXT TEAM_RED -->", $red_team, $output);
		$output = str_replace("<!-- TXT TEAM_BLUE -->", $blue_team, $output);
		$output = str_replace("<!-- TXT TEAM_GREEN -->", $green_team, $output);
		$output = str_replace("<!-- TXT SCORE_RED -->", number_format($team['red']['scores']), $output);
		$output = str_replace("<!-- TXT SCORE_BLUE -->", number_format($team['blue']['scores']), $output);
		$output = str_replace("<!-- TXT SCORE_GREEN -->", number_format($team['green']['scores']), $output);
		$output = str_replace("<!-- TXT KILL_RED -->", number_format($team['red']['kills']), $output);
		$output = str_replace("<!-- TXT KILL_BLUE -->", number_format($team['blue']['kills']), $output);
		$output = str_replace("<!-- TXT KILL_GREEN -->", number_format($team['green']['kills']), $output);
		$output = str_replace("<!-- TXT DEATH_RED -->", number_format($team['red']['deaths']), $output);
		$output = str_replace("<!-- TXT DEATH_BLUE -->", number_format($team['blue']['deaths']), $output);
		$output = str_replace("<!-- TXT DEATH_GREEN -->", number_format($team['green']['deaths']), $output);
		$output = str_replace("<!-- TXT RATIO_RED -->", @number_format(($team['red']['kills']/$team['red']['deaths']), 2, '.', ''), $output);
		$output = str_replace("<!-- TXT RATIO_BLUE -->", @number_format(($team['blue']['kills']/$team['blue']['deaths']), 2, '.', ''), $output);
		$output = str_replace("<!-- TXT RATIO_GREEN -->", @number_format(($team['green']['kills']/$team['green']['deaths']), 2, '.', ''), $output);

		echo $output;
	}else{
		echo "   Red Team: ". $server_name[$team['red']['host']] ." ".$guest_name['red']."\n";
		echo "    - Victory Point: ". number_format($team['red']['v_scores'], "0", "", ",") . "\n";
		echo "    - Current Skirmish Score: ". number_format($team['red']['current_skirmish_scores'], "0", "", ",") ."\n";
		echo "    - Total War Score: ". number_format($team['red']['scores'], "0", "", ",") ."\n";
		echo "    - Kills: ". number_format($team['red']['kills'], "0", "", ",") ."\n";
		echo "    - Deaths: ". number_format($team['red']['deaths'], "0", "", ",") ."\n";
		echo "    - KDR: ". @number_format(($team['red']['kills']/$team['red']['deaths']), 2, '.', '') ."\n";

		echo "   Blue Team: ". $server_name[$team['blue']['host']] ." ".$guest_name['blue']."\n";
		echo "    - Victory Point: ". number_format($team['blue']['v_scores'], "0", "", ",") . "\n";
		echo "    - Current Skirmish Score: ". number_format($team['blue']['current_skirmish_scores'], "0", "", ",") ."\n";
		echo "    - Total War Score: ". number_format($team['blue']['scores'], "0", "", ",") ."\n";
		echo "    - Kills: ". number_format($team['blue']['kills'], "0", "", ",") ."\n";
		echo "    - Deaths: ". number_format($team['blue']['deaths'], "0", "", ",") ."\n";
		echo "    - KDR: ". @number_format(($team['blue']['kills']/$team['blue']['deaths']), 2, '.', '') ."\n";

		echo "   Green Team: ". $server_name[$team['green']['host']] ." ".$guest_name['green']."\n";
		echo "    - Victory Point: ". number_format($team['green']['v_scores'], "0", "", ",") . "\n";
		echo "    - Current Skirmish Score: ". number_format($team['green']['current_skirmish_scores'], "0", "", ",") ."\n";
		echo "    - Total War Score: ". number_format($team['green']['scores'], "0", "", ",") ."\n";
		echo "    - Kills: ". number_format($team['green']['kills'], "0", "", ",") ."\n";
		echo "    - Deaths: ". number_format($team['green']['deaths'], "0", "", ",") ."\n";
		echo "    - KDR: ". @number_format(($team['green']['kills']/$team['green']['deaths']), 2, '.', '') ."\n";
	}

	unset($guest_name);
}

if($use_template){
	echo $template_footer;
}
?>