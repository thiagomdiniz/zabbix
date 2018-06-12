<?php

require_once dirname(__FILE__).'/include/config.inc.php';

// Get host ID from URL
$vhostid = $_GET['vhostid'];

// Output HTML
echo visGetHost($vhostid);

// Function to get trigger details via API
function visGetTrigger($triggerid) {

	if($triggerid) {

		$trigger = API::Trigger()->get([
			'triggerids' => $triggerid,
			'expandExpression' => true
		]);

		return $trigger;

	}

}

// Function to get user details via API
function visGetUser($userid) {

	if($userid) {

		$user = API::User()->get([
			'userids' => $userid,
			'output' => ["name","alias"]
		]);

		return $user[0]['alias'] . " (" . $user[0]['name'] .")";

	}

}

// Function to get host details via API and generate HTML
function visGetHost($vhostid) {

	if($vhostid) {

		$host = API::Host()->get([
			'output' => ["name","description"],
			'hostids' => $vhostid,
			'selectGroups' => ["name"],
			'selectItems' => count,
			'selectApplications' => count,
			'selectDiscoveries' => count,
			'selectGraphs' => count,
			'selectTriggers' => count,
			'selectParentTemplates' => ["name"]
		]);

		//print_r($host);
		//echo "</br>";

		$problems = API::Problem()->get([
			'hostids' => $vhostid,
			'outuput' => "extend",
			'selectAcknowledges' => "extend"
		]);

		//print_r($problems);
		//echo "</br>";

		$detail = "<style type=\"text/css\">\n" . 
			"	table, td {\n" .
			"		border: 1px solid grey;\n" .
			"		border-collapse: collapse;\n" .
			"		font-size: 12px;\n" .
			"		padding: 3px;\n" .
			"	}\n" .
			"	.names {\n" .
			"		text-align: right;\n" .
			"	}\n" .
			"	ul {\n" .
			"		padding-left: 12px;\n" .
			"	}\n" .
			"</style>\n" .
			"<p><b>Host details:</b></p>\n" .
			"<table>\n" .
			"	<tr><td class='names'>Name:</td><td>" .$host[0]['name'] . "</td></tr>\n" .
			"	<tr><td class='names'>Description:</td><td>" . $host[0]['description'] . "</td></tr>\n". 
			"	<tr><td class='names'>Groups:</td><td>" . count($host[0]['groups']) . " (";

		foreach($host[0]['groups'] as $group) {

			$grps[] = $group['name'];

		}

		$detail .= implode(", ", $grps) . ").</td></tr>" .
			"	<tr><td class='names'>Items:</td><td>" . $host[0]['items'] . "</td></tr>\n" .
			"	<tr><td class='names'>Applications:</td><td>" . $host[0]['applications'] . "</td></tr>\n" .
			"	<tr><td class='names'>Discoveries:</td><td>" . $host[0]['discoveries'] . "</td></tr>\n" .
			"	<tr><td class='names'>Graphs:</td><td>" . $host[0]['graphs'] . "</td></tr>\n" .
			"	<tr><td class='names'>Triggers:</td><td>" . $host[0]['triggers'] . "</td></tr>\n" .
			"	<tr><td class='names'>Parent Templates:</td><td>" . count($host[0]['parentTemplates']) . " (";

		foreach($host[0]['parentTemplates'] as $tmpl) {

			$tmpls[] = $tmpl['name'];

		}

		$detail .= implode(", ", $tmpls) . ").</td></tr>\n" .
			"	<tr><td class='names'>" . count($problems) . " Problems</td><td>\n";

		//print_r(visGetTrigger($problems[0]['objectid']));
		//echo "</br>";

		foreach($problems as $problem) {

			$trigger = visGetTrigger($problem['objectid']);

			$detail .= "	<ul><li>EventID: " . $problem['eventid'] . "</li>\n" .
				"		<li>Priority: " . $trigger[0]['priority'] . "</li>\n" .
				"		<li>Description: " . $trigger[0]['description'] . "</li>\n" .
				"		<li>Expression: \"<i>" . $trigger[0]['expression'] . "</i>\"</li>\n" .
				"		<li>Acks: " . count($problem['acknowledges']) . "</li>\n";

			foreach($problem['acknowledges'] as $ack) {

				$detail .= "		<ul><li>" . visGetUser($ack['userid']) . ": " .
					$ack['message'] . "		</li></ul>\n";

			}

			$detail .= "	</ul>\n";

		}

		$detail .= "	</td></tr>\n</table>";

		return $detail;

	} else {

		return "	<p>Select a host to view details.</p>";

	}

}

?>
