<?php
/**
 * Project: OTA Backend
 *
 * License: GNU General Public License, Version 3
 **/

/**
 * Copyright 2017 Tim Schumacher
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Get the root of the OTA installation
define("ROOT", str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

// Get the query sent as string
define('QUERY', str_replace(ROOT, '', $_SERVER['REQUEST_URI']));

// Get the query sent as array split by '/'
define('QUERY_ARR', explode('/', QUERY));

// Get all files in /builds directory
$builds = array_values(array_diff(scandir('builds'), array('.', '..', '.gitkeep')));
foreach ($builds as $key=>$build)
{
    $build = str_replace(".zip", "", $build);
    $builds[$key] = explode("-", $build);
}
define('BUILDS', $builds);
unset($build, $builds, $key);

function get_device_builds($device)
{
    if ($device !== "")
    {
        foreach (BUILDS as $build)
        {
            if($build[4] === $device)
            {
                $builds[] = $build;
            }
        }
    }
    else
    {
        $builds = BUILDS;
    }

    usort($builds, function($a, $b)
    {
        if ($a[2] == $b[2]) {
            return 0;
        }
        return ($a[2] < $b[2]) ? 1 : -1;
    });

    return $builds;
}

if(QUERY_ARR[0] === "api")
{
    // Trigger API
    $device = (QUERY_ARR[1] ?: "");
    $builds = get_device_builds($device);
    echo "{\n";
    if(count($builds) !== 0)
    {
        echo "  \"result\": [\n";
        for($key = 0; $key < count($builds); $key++)
        {
            $buildname = $builds[$key][0]."-".$builds[$key][1]."-".$builds[$key][2]."-".$builds[$key][3]."-".$builds[$key][4].".zip";
            echo "    {\n";
            echo "      \"name\": \"".$builds[$key][0]."\",\n";
            echo "      \"version\": \"".$builds[$key][1]."\",\n";
            echo "      \"date\": \"".$builds[$key][2]."\",\n";
            echo "      \"channel\": \"".$builds[$key][3]."\",\n";
            echo "      \"device\": \"".$builds[$key][4]."\",\n";
            echo "      \"md5sum\": \"".md5_file("builds/".$buildname)."\",\n";
            echo "      \"filename\": \"".$buildname."\",\n";
            echo "      \"changelog\": \"".(file_exists($buildname.".txt") ? file_get_contents($buildname.".txt") : "")."\"\n";
            echo "    }".($key < count($builds)-1 ? "," : "")."\n";
        }
        echo "  ]\n";
    }
    echo "}";
}
else
{
    // Trigger user interface
    $device = (QUERY_ARR[0] ?: "");
    $builds = get_device_builds($device);?>
<!DOCTYPE html>
<html>
    <head>
        <title>OTA Server</title>
    </head>
    <body>
        <h1>OTA Updates</h1>
        <?php
        if (count($builds) === 0)
        {
            echo "No builds";
        }
        else
        {
            echo "<table>\n";
            echo "<tr><th>ROM</th><th>Version</th><th>Date</th><th>Device</th><th>Channel</th><th>Download</th><th>MD5</th></tr>\n";
            foreach ($builds as $build)
            {
                $buildname = $build[0]."-".$build[1]."-".$build[2]."-".$build[3]."-".$build[4].".zip";
                echo "<tr><td>".$build[0]."</td><td>".$build[1]."</td><td>".$build[2]."</td><td>".$build[4]."</td><td>".$build[3]."</td><td><a href='".ROOT."builds/".$buildname."'>".$buildname."</a></td><td>".md5_file("builds/".$buildname)."</td></tr>\n";
            }
            echo "</table>";
        }
    ?>
    </body>
</html>
<?php
}

/*
// Debug stuff
echo "<pre>";
echo "ROOT: ".ROOT."\n";
echo "QUERY: ".QUERY."\n";
echo "QUERY_ARR: ".print_r(QUERY_ARR, true);
echo "BUILDS: ".print_r(BUILDS, true);
echo "</pre>";
*/