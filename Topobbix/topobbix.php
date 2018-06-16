<?php

require_once dirname(__FILE__).'/include/config.inc.php';

// Check logged user
if (!CWebUser::$data['alias'] || CWebUser::$data['alias'] == ZBX_GUEST_USER) {

	htmlBegin();
	echo "		<p>No user logged on Zabbix Frontend!</p>\n\n" .
		"		<div id=\"mynetwork\"></div>\n" .
		"		<div id=\"eventSpan\"></div>\n\n" .
		"		<script type=\"text/javascript\">\n\n";
	htmlEnd();
	exit();

} else {

	// Read GET Topobbix URL parameters
	if (isset($_GET['hostgroup'])) {

		$hostgroup = $_GET['hostgroup'];
		$vhostid = $_GET['vhostid'];

		// Check user permissions for the HostGroup
		$grupo = API::HostGroup()->get([
			'output' => ['name'],
			'filter' => ['name' => $hostgroup],
			'countOutput' => 'true'
		]);

		if(!$grupo) {

			htmlBegin();
			echo "		<p>You don't have permission to read this HostGroup or this HostGroup not exists!</p>\n\n" .
				"		<div id=\"mynetwork\"></div>\n" .
				"		<div id=\"eventSpan\"></div>\n\n" .
				"		<script type=\"text/javascript\">\n\n";
			htmlEnd();
			exit();

		}

	} else {

		htmlBegin();
		echo "		<p>No HostGroup selected.</p>\n\n" .
			"		<div id=\"mynetwork\"></div>\n" .
			"		<div id=\"eventSpan\"></div>\n\n" .
			"		<script type=\"text/javascript\">\n\n";
		htmlEnd();
		exit();

	}

}

// Function to print initial HTML and vis.js code
function htmlBegin() {

	global $hostgroup;

	echo "<html>\n" .
		"	<head>\n" .
		"		<title>Topobbix | Zabbix Topology Viewer</title>\n" .
		"		<script type=\"text/javascript\" src=\"topobbix/vis.min.js\"></script>\n" .
		"		<link href=\"topobbix/vis.min.css\" rel=\"stylesheet\" type=\"text/css\" />\n" .
		"		<script type=\"text/javascript\" src=\"js/vendors/jquery.js\"></script>\n" .
		"		<style type=\"text/css\">\n" .
		"			#mynetwork {\n" .
		"				width: 70%;\n" .
		"				height: 85%;\n" .
		"				border: 1px solid lightgray;\n" .
		"				float: left;\n" .
		"				background-color: #dddddd;\n" .
		"			}\n" .
		"			#eventSpan {\n" .
		"				width: 28%;\n" .
		"				height: 83%;\n" .
		"				border: 1px solid lightgray;\n" .
		"				float: left;\n" .
		"				font-size: 12px;\n" .
		"				overflow-y: scroll;\n" .
		"				padding: 5px 5px 5px 5px;\n" .
		"			}\n" .
		"			#hg {\n" .
		"				color: #f2f2f2;\n" .
		"				background-color: #383838;\n" .
		"				border: 1px solid #4f4f4f;\n" .
		"				padding: 3px 3px 3px 0;\n" .
		"			}\n" .
		"			.legend {\n" .
		"				font-size: 12px;\n" .
		//"				font-weight: bold;\n" .
		"				padding:2px 2px 2px 2px;\n" .
		"				color: #0e1012;\n" .
		"			}\n" .
		"			body {\n" .
		"				background-color: #0e1012;\n" .
		"				color: #f2f2f2;\n" .
		"				font-family: Arial, Tahoma, Verdana, sans-serif;\n" .
		"			}\n" .
		"		</style>\n" .
		"	</head>\n" .
		"	<body>\n" .
		"		<div id=\"topo\"><p>Topobbix - Host Group:\n" .
		"		<select id=\"hg\">\n" .
		"			<option value=\"\" selected>None</option>\n";

	$groups = API::HostGroup()->get([
		'output' => ['name']
	]);

	foreach($groups as $group) {

		if(strpos($group['name'], "Templates") === false) {

			echo "			<option value=\"" . $group['name'] . "\"" .
				(($group['name'] == $hostgroup) ? " selected" : "") .
				">" . $group['name'] . "</option>\n";

		}

	}

	echo "		</select></p>\n\n";

}

// Function to print the vis.js conf code
function htmlVis() {

	global $vhostid;

	echo "			// create a network\n" .
		"			var container = document.getElementById('mynetwork');\n" .
		"			var data = {\n" .
		"				nodes: nodes,\n" .
		"				edges: edges\n" .
		"			};\n" .
		//"			var options = {};\n" .
		"			var options = {\n" .
		"				nodes: {\n" .
		//"					shadow: true,\n" .
		"					margin: 10,\n" .
		"					shape: 'box',\n" .
		"					font: { size: 20 },\n" .
		"					shapeProperties: {\n" .
		"						useBorderWithImage:true\n" .
		"					},\n" .
		"					widthConstraint: { maximum: 150 }\n" .
		"				},\n" .
		"				layout: {\n" .
		//"					randomSeed: undefined,\n" .
		//"					improvedLayout:false,\n" .
		"					hierarchical: {\n" .
		"						enabled:true,\n" .
		//"						levelSeparation: 110,\n" .
		"						nodeSpacing: 200,\n" .
		//"						treeSpacing: 200,\n" .
		//"						blockShifting: false,\n" .
 		//"						edgeMinimization: false,\n" .
		//"						parentCentralization: true,\n" .
		"						direction: 'UD',        // UD, DU, LR, RL\n" .
		"						sortMethod: 'hubsize'   // hubsize, directed\n" .
		"					}\n" .
		"				}\n" .
		//"			interaction: {dragNodes :false}\n" .
		//"			physics: {\n" .
		//"				enabled: true,\n" .
		//"				repulsion: {\n" .
		//"				nodeDistance: 400\n" .
		//"			}\n" .
		//"		}\n" .
		"			};\n\n" .
		"			var network = new vis.Network(container, data, options);\n\n" .
		"			network.on(\"click\", function (params) {\n" .
		"				$(\"#eventSpan\").load(\"topobbix.detail.php?vhostid=\" + params.nodes);\n" .
		"			});\n\n";

	if($vhostid) {

		echo "			$(\"#eventSpan\").load(\"topobbix.detail.php?vhostid=" . $vhostid . "\");\n". 
			"			network.selectNodes([" . $vhostid . "]);\n";
	}

}

// Function to print the HTML end code
function htmlEnd() {

	global $hostgroup;

	echo "			$(document).on({\n" .
		"				ajaxStart: function() { $(\"#eventSpan\").html(\"<h3>loading...</h3>\");    },\n" .
		//"				ajaxStop: function() { $(\"#eventSpan\").removeClass(\"loading\"); }\n" .
		"			});\n\n" .
		"			$(\"#hg\").change(function(){\n" .
		"				window.open(\"topobbix.php?hostgroup=\" + $( this ).val(), \"_self\");\n" .
		"			});\n\n" .
		"		</script>\n" .
		"	</body>\n" .
		"</html>";

}

// SQL query for severity colors config
$severityColorSQL = "select severity_name_0, severity_color_0, " .
	"severity_name_1, severity_color_1, " .
	"severity_name_2, severity_color_2, " .
	"severity_name_3, severity_color_3, " .
	"severity_name_4, severity_color_4, " .
	"severity_name_5, severity_color_5 " .
	"from config";

// SQL query for hosts dependencies
switch ($DB['TYPE']){

	case ZBX_DB_MYSQL:

		$dependsSQL = "select * from " .

			"(select distinct " .
			"g.name as 'hostgroup', h.name as 'host', h.hostid as 'hostid', t.description as 'trigger', t.value as 'triggered', " .

			"(select max(t1.priority) " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where h1.hostid = h.hostid and t1.value = 1) as maxseverity, " .

			"(select distinct h1.name " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as depends, " .

			"(select distinct h1.hostid " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as depid, " .

			"(select distinct t1.description " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as deptrigger, " .

			"(select distinct t1.value " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			 "where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as deptriggered, " .

			"(select max(t1.priority) " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where h1.hostid = DepID and t1.value = 1) as maxdepseverity " .

			"from groups g " .
			"join hosts_groups hg on g.groupid = hg.groupid " .
			"join hosts h on hg.hostid = h.hostid " .
			"join items i on h.hostid = i.hostid " .
			"join functions f on i.itemid = f.itemid " .
			"join triggers t on f.triggerid = t.triggerid " .
			"left join trigger_depends td on t.triggerid = td.triggerid_down " .

			"where g.name = '$hostgroup' and h.status = 0 and t.status = 0) as c ";
			//"where depends is not null and host != depends";

		break;

	case ZBX_DB_POSTGRESQL:

		$dependsSQL = "select * from " .

			"(select distinct " .
			"g.name as hostgroup, h.name as host, h.hostid as hostid, t.description as trigger, t.value as triggered, " .

			"(select max(t1.priority) " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where h1.hostid = h.hostid and t1.value = 1) as maxseverity, " .

			"(select distinct h1.name " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as depends, " .

			"(select distinct h1.hostid " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as depid, " .

			"(select distinct t1.description " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as deptrigger, " .

			"(select distinct t1.value " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) as deptriggered, " .

			"(select max(t1.priority) " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where h1.hostid = (select distinct h1.hostid " .
			"from hosts_groups hg1 " .
			"join hosts h1 on hg1.hostid = h1.hostid " .
			"join items i1 on h1.hostid = i1.hostid " .
			"join functions f1 on i1.itemid = f1.itemid " .
			"join triggers t1 on f1.triggerid = t1.triggerid " .
			"where t1.triggerid = td.triggerid_up and hg1.groupid = g.groupid) and t1.value = 1) as maxdepseverity " .

			"from groups g " .
			"join hosts_groups hg on g.groupid = hg.groupid " .
			"join hosts h on hg.hostid = h.hostid " .
			"join items i on h.hostid = i.hostid " .
			"join functions f on i.itemid = f.itemid " .
			"join triggers t on f.triggerid = t.triggerid " .
			"left join trigger_depends td on t.triggerid = td.triggerid_down " .

			"where g.name = '$hostgroup' and h.status = 0 and t.status = 0) as c ";
			//"where depends is not null and host != depends";

		break;

	default:

		echo "Unsupported database!";
		exit();

}

// Create an array with severity names and colors defined on Zabbix config
$result = DBselect($severityColorSQL);
$severity_colors = array();

while($color = DBfetch($result, $convertNulls = false)) {

	$severity_colors[] = [ "name" => $color['severity_name_0'], "color" => $color['severity_color_0'] ];
	$severity_colors[] = [ "name" => $color['severity_name_1'], "color" => $color['severity_color_1'] ];
	$severity_colors[] = [ "name" => $color['severity_name_2'], "color" => $color['severity_color_2'] ];
	$severity_colors[] = [ "name" => $color['severity_name_3'], "color" => $color['severity_color_3'] ];
	$severity_colors[] = [ "name" => $color['severity_name_4'], "color" => $color['severity_color_4'] ];
	$severity_colors[] = [ "name" => $color['severity_name_5'], "color" => $color['severity_color_5'] ];

}

// Create an array with Zabbix triggers dependencies
$result = DBselect($dependsSQL);
$depends = array();

while ($depend = DBfetch($result, $convertNulls = false)) {

	$depends[] = ["HostID" => $depend['hostid'],
		"Host" => $depend['host'],
		"Trigger" => $depend['trigger'],
		"Triggered" => $depend['triggered'],
		"MaxSeverity" => $depend['maxseverity'],
		"DepID" => $depend['depid'],
		"Depends" => $depend['depends'],
		"DepTrigger" => $depend['deptrigger'],
		"DepTriggered" => $depend['deptriggered'],
		"MaxDepSeverity" => $depend['maxdepseverity'] ];

}

// If no results returned
$size = count($depends);

if($size == 0) {

	htmlBegin();
	echo "		<p>No results for hostgroup $hostgroup.</p>\n\n" .
		"		<div id=\"mynetwork\"></div>\n" .
		"		<div id=\"eventSpan\"></div>\n\n" .
		"		<script type=\"text/javascript\">\n\n";
	htmlEnd();
	exit();

// If results are returned
} else {

	htmlBegin();

	// Print HTML code for colors legend
	echo '		<p><span style="font-size:12px">Color legend: </span>' .
		"\n" . '		<span class="legend" style="background-color:#3ADF00">OK</span> - ' .
		"\n" . '		<span class="legend" style="background-color:#' . 
		$severity_colors['0']['color'] . '">' . '0-' . $severity_colors['0']['name'] . '</span> - ' .
		"\n" . '		<span class="legend" style="background-color:#' . 
		$severity_colors['1']['color'] . '">' . '1-' . $severity_colors['1']['name']  . '</span> - ' .
		"\n" . '		<span class="legend" style="background-color:#' . 
		$severity_colors['2']['color'] . '">' . '2-' . $severity_colors['2']['name']  . '</span> - ' .
		"\n" . '		<span class="legend" style="background-color:#' . 
		$severity_colors['3']['color'] . '">' . '3-' . $severity_colors['3']['name']  . '</span> - ' .
		"\n" . '		<span class="legend" style="background-color:#' . 
		$severity_colors['4']['color'] . '">' . '4-' . $severity_colors['4']['name']  . '</span> - ' .
		"\n" . '		<span class="legend" style="background-color:#' . 
		$severity_colors['5']['color'] . '">' . '5-' . $severity_colors['5']['name']  . '</span></p></div>';

	// Print begin code of vis.js topology
	echo "\n\n		<div id=\"mynetwork\"></div>\n" .
		"		<div id=\"eventSpan\">Click on any host to view the details.</div>\n\n" .
		"		<script type=\"text/javascript\">\n" .
		"			// create an array with nodes\n" .
		"			var nodes = new vis.DataSet([\n";

	// Variable for nodes print control
	$linkHosts = array();
	$hostLines = array();

	// Loop for print vis.js node code
	foreach($depends as $line) {

		if(!in_array($line['HostID'], $linkHosts)) {

			$color = '3ADF00';

			if($line['MaxSeverity'] != NULL) {

				$color = $severity_colors[$line['MaxSeverity']]['color'];

			}

			$hostLines[] = "				{id: " . $line['HostID'] . ", label: \"" . $line['Host'] . "\", color: '#" . $color . "'}";
			$linkHosts[] = $line['HostID'];

		}

		if($line['DepID'] && !in_array($line['DepID'], $linkHosts)) {

			$color = '3ADF00';

			if($line['MaxDepSeverity'] != NULL) {

				$color = $severity_colors[$line['MaxDepSeverity']]['color'];

			}

			//$hostLines[] = "				{id: " . $line['DepID'] . ", label: \"" . $line['Depends'] . "\", color: '#" . $color . "', image: 'http://192.168.25.250/zabbix/imgstore.php?iconid=1', shape: 'image'}";
			$hostLines[] = "				{id: " . $line['DepID'] . ", label: \"" . $line['Depends'] . "\", color: '#" . $color . "'}";
			$linkHosts[] = $line['DepID'];

		}

		if ($line === end($depends)) {

			echo implode(",\n", $hostLines);
			echo "\n			]);\n";

		}

	}

	// Variable for nodes edges control
	$linkHosts = array();
	$nodeLines = array();
	echo "			// create an array with edges\n" .
		"			var edges = new vis.DataSet([\n";

	// Loop for print vis.js edges code
	foreach($depends as $line) {

		if($line['DepID'] && !in_array($line['HostID'] . $line['DepID'], $linkHosts)) {

			$nodeLines[] = "				{from: " . $line['HostID'] . ", to: " . $line['DepID'] . ", arrows:'to'}";

		}

		if ($line === end($depends)) {

			echo implode(",\n", $nodeLines);
			echo "\n			]);\n";

		}

	}

	htmlVis();
	htmlEnd();

}

?>
