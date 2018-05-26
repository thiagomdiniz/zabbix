<?php

require_once dirname(__FILE__) . '/include/config.inc.php';

// Read GET Topobbix URL parameters
if (isset($_GET['hostgroup'])) {

    $hostgroup = $_GET['hostgroup'];
    $triggernames = $_GET['triggernames'];

} else {

    echo "Oh no! No hostgroup defined.";
    exit();

}

// Function to print initial HTML code
function htmlBegin(){

    global $hostgroup;

    echo "<html>\n" .
         "  <head>\n" .
         "    <title>Topobbix</title>\n" .
         "    <script src='https://unpkg.com/mermaid@7.1.2/dist/mermaid.min.js'></script>\n" .
         "    <script>mermaid.initialize({startOnLoad:true});</script>\n" .
         "  </head>\n" .
         "  <body>\n" .
         "    <h4>Topobbix - Host Group: $hostgroup</h4>\n";
}

// Function to print the end of HTML code
function htmlEnd(){

    echo "\n    </div>\n" .
         "  </body>\n" .
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

          "where g.name = '$hostgroup' and h.status = 0 and t.status = 0) as c " .
          "where depends is not null and host != depends";
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

          "where g.name = '$hostgroup' and h.status = 0 and t.status = 0) as c " .
          "where depends is not null and host != depends";
        break;

    default:

        echo "Unsupported database!";
        exit();

}

// Create an array with severity names and colors defined on Zabbix config
$result = DBselect($severityColorSQL);
$severity_colors = array();
while($color = DBfetch($result, $convertNulls = false)){

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
if($size == 0){

    htmlBegin();
    echo "<div class='mermaid'>\ngraph BT\n";
    echo "A(No results for $hostgroup) --> B(Oh no!)";
    htmlEnd();

// If results are returned
} else {

    htmlBegin();

    // Print HTML code for colors legend
    echo '    <span style="font-size:12px">Color legend: </span>' .
         "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#3ADF00">OK</span> - ';
    echo "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#' . 
          $severity_colors['0']['color'] . '">' . $severity_colors['0']['name'] . '</span> - ';
    echo "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#' . 
          $severity_colors['1']['color'] . '">' . $severity_colors['1']['name']  . '</span> - ';
    echo "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#' . 
          $severity_colors['2']['color'] . '">' . $severity_colors['2']['name']  . '</span> - ';
    echo "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#' . 
          $severity_colors['3']['color'] . '">' . $severity_colors['3']['name']  . '</span> - ';
    echo "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#' . 
          $severity_colors['4']['color'] . '">' . $severity_colors['4']['name']  . '</span> - ';
    echo "\n" . '    <span style="font-size:12px;font-weight:bold;background-color:#' . 
          $severity_colors['5']['color'] . '">' . $severity_colors['5']['name']  . '</span>';

    echo "\n    <hr/>\n    <div class='mermaid'>\n    graph BT\n";

    // Variables for style control
    $problemHosts = array();
    $okHosts = array();

    // Variable for nodes link control
    $linkHosts = array();

    // Loop for print MermaidJS code
    foreach($depends as $line){

        // If GET variable "triggernames" is set, print topology with trigger names on dependencies links.
        if($triggernames == 1){

            echo $line['HostID'] . "(" .
                 $line['Host'] . ")--" .
                 $line['DepTrigger'] . "<br/><br/>" .
                 $line['Trigger'] . " -->" .
                 $line['DepID'] . "(" .
                 $line['Depends'] . ")\n";

        // If GET variable "triggernames" is not set and the hosts are not linked before.
        } elseif(!in_array($line['HostID'] . $line['DepID'], $linkHosts)) {

            echo $line['HostID'] . "(" .
                 $line['Host'] . ")-->" .
                 $line['DepID'] . "(" .
                 $line['Depends'] . ")\n";

            $linkHosts[] = $line['HostID'] . $line['DepID'];

        }

        // Print style for MermaidJS nodes.
        if($line['MaxSeverity'] != NULL && !in_array($line['HostID'], $problemHosts)){

            echo "style " . $line['HostID'] . " fill:#" . $severity_colors[$line['MaxSeverity']]['color'] . "\n";
            $problemHosts[] = $line['HostID'];

        } elseif(!in_array($line['HostID'], $okHosts) && !in_array($line['HostID'], $problemHosts)) {

            echo "style " . $line['HostID'] . " fill:#3ADF00\n";
            $okHosts[] = $line['HostID'];

        }
        if($line['MaxDepSeverity'] != NULL && !in_array($line['DepID'], $problemHosts)) {

            echo "style " . $line['DepID'] . " fill:#" . $severity_colors[$line['MaxDepSeverity']]['color'] . "\n";
            $problemHosts[] = $line['DepID'];

        } elseif(!in_array($line['DepID'], $okHosts) && !in_array($line['DepID'], $problemHosts)) {

            echo "style " . $line['DepID'] . " fill:#3ADF00\n";
            $okHosts[] = $line['DepID'];

        }

    }

    htmlEnd();

}

?>
