<?php

function printGrid($grid, $gridSize)
{
    [$cols, $rows] = $gridSize;
    for ($y=0; $y<$rows; $y++) {
        $row = '';
        for ($x=0; $x<$cols; $x++) {
            $row .= $grid[$y][$x] ?? '.';
        }
        echo "$row\n";
    }
}

function distance($a, $b)
{
    return abs($a[0] - $b[0]) + abs($a[1] - $b[1]);
}

function parseLine($line)
{
    //Sensor at x=3844106, y=3888618: closest beacon is at x=3225436, y=4052707
    $matches = [];
    preg_match('/^Sensor at x=(-?[0-9]+), y=(-?[0-9]+): closest beacon is at x=(-?[0-9]+), y=(-?[0-9]+)$/', $line, $matches);
    return [[$matches[1], $matches[2]], [$matches[3], $matches[4]]];
}

$lines = file('input.txt', FILE_IGNORE_NEW_LINES);

$examine = 2000000;
$maxX = 0;
$maxY = 0;
$minX = PHP_INT_MAX;
$minY = PHP_INT_MAX;
$beacons = [];
$sensors = [];
foreach ($lines as $line) {
    [$sensor, $beacon] = parseLine($line);
    $distance = distance($sensor, $beacon);
    if ($examine >= ($sensor[1] - $distance) && $examine <= ($sensor[1] + $distance)) {
        $beacons[] = $beacon;
        $sensors[] = [...$sensor, $distance];
        $maxY = max($maxY, $sensor[1]+$distance);
        $minY = min($minY, $sensor[1]-$distance);
        $maxX = max($maxX, $sensor[0]+$distance);
        $minX = min($minX, $sensor[0]-$distance);
    }
}

$grid = [];
$gridSize = [$maxX - $minX + 1, $maxY - $minY + 1];
print_r([$minX, $maxX, $minY, $maxY]);

foreach ($sensors as [$sensorX, $sensorY, $distance]) {
    $sensorPos = [$sensorX, $sensorY];
    $grid[$sensorY - $minY][$sensorX - $minX] = 'S';
    $y = $examine;
    for ($x=max($minX, $sensorX - $distance); $x<=min($maxX, $sensorX+$distance); $x++) {
        if (!isset($grid[$y-$minY][$x-$minX]) && distance($sensorPos, [$x, $y]) <= $distance) {
            $grid[$y-$minY][$x-$minX] = '#';
        }
    }
}

foreach ($beacons as [$beaconX, $beaconY]) {
    $grid[$beaconY - $minY][$beaconX - $minX] = 'B';
}

//printGrid($grid, $gridSize);
$row = $grid[$examine - $minY];
print_r(array_filter($row, fn($i) => $i !== '#'));
$result = array_reduce($row, function($carry, $item) {
    if ($item === '#' || $item === 'S') {
        $carry += 1;
    }
    return $carry;
}, 0);

echo "Not able to place a beacon in $result positions\n";
