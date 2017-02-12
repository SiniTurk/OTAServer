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

function get_ota_builds($device, $timestamp = false)
{
    $builds = array();

    if ($device)
    {
        foreach (BUILDS as $build)
        {
            if(strtolower($build['device']) === strtolower($device) && (!$timestamp || $timestamp <= $build['timestamp']))
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