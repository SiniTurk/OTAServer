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

// Set the name of the folder, where the delta updates are stored
define("DELTA_FOLDER", "deltas");

// Get the HTML root of the OTA installation
define("HTML_ROOT", str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

// Get the PHP root of the OTA installation
define("PHP_ROOT", $_SERVER['DOCUMENT_ROOT'].HTML_ROOT);

// Get the query sent as string
define('QUERY', str_replace(HTML_ROOT, '', $_SERVER['REQUEST_URI']));

// Get the query sent as array split by '/'
define('QUERY_ARR', explode('/', QUERY));

require_once 'inc/functions.inc';

// Get all builds in the specific directory
$builds = array();
if (strtolower(QUERY_ARR[0]) === "delta")
{
    foreach (array_values(array_diff(scandir(DELTA_FOLDER), array('.', '..', '.gitkeep'))) as $build) {
        if (does_file_exist("zip://" . DELTA_FOLDER . "/" . $build . "#META-INF/com/android/metadata"))
        {
            $props = read_buildprop_value("zip://" . DELTA_FOLDER . "/" . $build . "#META-INF/com/android/metadata");
            $builds[] = array(
                "filename" => $build,
                "old_incremental" => $props["pre-build-incremental"],
                "new_incremental" => $props["post-build-incremental"],
                "timestamp" => $props['post-timestamp'],
                "md5sum" => md5_file(DELTA_FOLDER . "/" . $build),
                "size" => filesize(DELTA_FOLDER . "/" . $build)
            );
        }
    }
}
else {
    foreach (array_values(array_diff(scandir(BUILD_FOLDER), array('.', '..', '.gitkeep'))) as $build) {

        if (does_file_exist("zip://" . BUILD_FOLDER . "/" . $build . "#system/build.prop")) {
            $props = read_buildprop_value("zip://" . BUILD_FOLDER . "/" . $build . "#system/build.prop");

            $builds[] = array(
                "filename" => $build,
                "device" => $props['ro.product.device'],
                "incremental" => $props['ro.build.version.incremental'],
                "timestamp" => $props['ro.build.date.utc'],
                "version" => $props['ro.build.version.release'],
                "md5sum" => md5_file(BUILD_FOLDER . "/" . $build),
                "size" => filesize(BUILD_FOLDER . "/" . $build)
            );
        }
    }
}
define('BUILDS', $builds);
unset($build, $builds, $key);

if(strtolower(QUERY_ARR[0]) === "api")
{
    // Trigger API
    $builds = get_ota_builds(QUERY_ARR[1] ?: false, QUERY_ARR[2] ?: false);
    echo json_encode($builds);
}
else
{
    if (strtolower(QUERY_ARR[0]) === "delta")
    {
        // Trigger Delta API
        $builds = get_delta_builds(QUERY_ARR[1] ?: false);
        echo json_encode($builds);
    }
    else
    {
        // Trigger user interface
        $builds = get_ota_builds(QUERY_ARR[0] ?: false, QUERY_ARR[1] ?: false);?>
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
                echo "<tr><td>".$build['version']."</td><td>".date("Y-m-d h:i", $build['timestamp'])."</td><td>".$build['device']."</td><td><a href='".HTML_ROOT.BUILD_FOLDER."/".$build['filename']."'>".$build['filename']."</a>  (".human_filesize($build["size"]).")<br><small>MD5: ".$build['md5sum']."</small></td></tr>\n";
            }
            echo "</table>";
        }
        ?>
        </body>
        </html>
        <?php
    }
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