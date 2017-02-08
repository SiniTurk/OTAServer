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

// Set the name of the folder, where the builds are stored
define("BUILD_FOLDER", "builds");

// Get the HTML root of the OTA installation
define("HTML_ROOT", str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

// Get the PHP root of the OTA installation
define("PHP_ROOT", $_SERVER['DOCUMENT_ROOT'].HTML_ROOT);

// Get the query sent as string
define('QUERY', str_replace(HTML_ROOT, '', $_SERVER['REQUEST_URI']));

// Get the query sent as array split by '/'
define('QUERY_ARR', explode('/', QUERY));

// Get all builds in /builds directory
$builds = array();
foreach (array_values(array_diff(scandir(BUILD_FOLDER), array('.', '..', '.gitkeep'))) as $build)
{
    // Get build.prop values
    $buildprops = @file_get_contents("zip://".BUILD_FOLDER."/".$build."#system/build.prop");

    if($buildprops !== FALSE)
    {
        // Filter build.prop values
        $buildprops = preg_replace('/\n\s*\n/', "\n", $buildprops);
        $buildprops = preg_replace('/\#(.*)\n/', "", $buildprops);
        $buildprops = explode("\n", $buildprops);
        $props = array();
        foreach ($buildprops as $prop)
        {
            $props[explode("=", $prop)[0]] = explode("=", $prop)[1];
        }
        unset($buildprops, $prop, $props['']);

        $builds[] = array(
            "filename"     => $build,
            "device"       => $props['ro.product.device'],
            "model"        => $props['ro.product.model'],
            "manufacturer" => $props['ro.product.manufacturer'],
            "timestamp"    => $props['ro.build.date.utc'],
            "version"      => $props['ro.build.version.release'],
            "md5sum"       => md5_file(BUILD_FOLDER."/".$build)
        );
    }
}
define('BUILDS', $builds);
unset($build, $builds, $key);

function get_device_builds($device)
{
    $builds = array();
    if ($device !== "")
    {
        foreach (BUILDS as $build)
        {
            if($build['device'] === $device)
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
        if ($a['timestamp'] == $b['timestamp']) {
            return 0;
        }
        return ($a['timestamp'] < $b['timestamp']) ? 1 : -1;
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
            echo "    {\n";
            $counter = 0;
            foreach ($builds[$key] as $setting=>$value)
            {
                $counter++;
                echo "      \"".$setting."\": \"".$value."\"".($counter < count($builds[$key]) ? "," : "")."\n";
            }
            unset($counter);
            echo "    }".($key < count($builds)-1 ? "," : "")."\n";
        }
        unset($key);
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
            echo "<tr><th>Version</th><th>Date</th><th>Device</th><th>Download</th></tr>\n";
            foreach ($builds as $build)
            {
                echo "<tr><td>".$build['version']."</td><td>".date("Y-m-d h:i", $build['timestamp'])."</td><td>".$build['device']."</td><td><a href='".HTML_ROOT.BUILD_FOLDER."/".$build['filename']."'>".$build['filename']."</a><br><small>MD5: ".$build['md5sum']."</small></td></tr>\n";
            }
            echo "</table>";
        }
    ?>
    </body>
</html>
<?php
}

/*// Debug stuff
echo "<pre>";
echo "PHP_ROOT: ".PHP_ROOT."\n";
echo "HTML_ROOT: ".HTML_ROOT."\n";
echo "QUERY: ".QUERY."\n";
echo "QUERY_ARR: ".print_r(QUERY_ARR, true);
echo "BUILDS: ".print_r(BUILDS, true);
echo "\$_SERVER: ".print_r($_SERVER, true);
echo "</pre>";
*/