<?php

class SparseGridRow
{
    private int $min = 0;
    private int $max = 0;
    private array $unavailableRanges = [];

    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function markUnavailable($start, $end)
    {
        if ($start > $end) {
            $tmp = $end;
            $end = $start;
            $start = $tmp;
        }
        if ($start > $this->max || $end < $this->min) {
            return;
        }
        $start = max($start, $this->min);
        $end = min($end, $this->max);

        $found = false;
        foreach ($this->unavailableRanges as $index => $range) {
            if ($start <= $range[1] && $end >= $range[0]) {
                $this->unavailableRanges[$index] = [min($start, $range[0]), max($end, $range[1])];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->unavailableRanges[] = [$start, $end];
        }

        usort($this->unavailableRanges, fn ($a, $b) => $a[0] <=> $b[0]);

        $res = [];
        $ranges = $this->unavailableRanges;
        $current = array_shift($ranges);
        foreach ($ranges as $range)
        {
            if ($range[0] <= $current[1] + 1) {
                $current[1] = max($current[1], $range[1]);
            } else {
                $res[] = $current;
                $current = $range;
            }
        }
        $res[] = $current;

        $this->unavailableRanges = $res;
    }

    public function getFirstUnavailable()
    {
        if (count($this->unavailableRanges) == 1 && $this->unavailableRanges[0][0] === $this->min && $this->unavailableRanges[0][1] === $this->max) {
            return null;
        }

        if (count($this->unavailableRanges) == 0 || $this->unavailableRanges[0][0] > $this->min) {
            return $this->min;
        }

        return $this->unavailableRanges[0][1] + 1;
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

$examineMin = 0;
$examineMax = 4000000;
$maxX = 0;
$maxY = 0;
$minX = PHP_INT_MAX;
$minY = PHP_INT_MAX;
$beacons = [];
$sensors = [];
foreach ($lines as $line) {
    [$sensor, $beacon] = parseLine($line);
    $distance = distance($sensor, $beacon);
    if (
        ($sensor[1] - $distance) <= $examineMax &&
        ($sensor[1] + $distance) >= $examineMin &&
        ($sensor[0] - $distance) <= $examineMax &&
        ($sensor[0] + $distance) >= $examineMin
    ) {
        $beacons[] = $beacon;
        $sensors[] = [...$sensor, $distance];
        $maxY = max($maxY, $sensor[1]+$distance);
        $minY = min($minY, $sensor[1]-$distance);
        $maxX = max($maxX, $sensor[0]+$distance);
        $minX = min($minX, $sensor[0]-$distance);
    }
}

$minX = max($examineMin, $minX);
$minY = max($examineMin, $minY);
$maxX = min($examineMax, $maxX);
$maxY = min($examineMax, $maxY);

$grid = [];
for ($y=$minY; $y<=$maxY; $y++) {
    $grid[$y-$minY] = new SparseGridRow($minX, $maxX);
}

foreach ($sensors as [$sensorX, $sensorY, $distance]) {
    $sensorPos = [$sensorX, $sensorY];
    for ($y=max($minY, $sensorY-$distance); $y<=min($maxY, $sensorY+$distance); $y++) {
        $distanceY = abs($sensorY - $y);
        $distanceX = $distance - $distanceY;
        $grid[$y-$minY]->markUnavailable($sensorX - $distanceX, $sensorX + $distanceX);
    }
}

foreach ($beacons as [$beaconX, $beaconY]) {
    if (isset($grid[$beaconY - $minY])) {
        $grid[$beaconY - $minY]->markUnavailable($beaconX, $beaconX);
    }
}

$possible = [];
foreach ($grid as $y => $row) {
    $x = $row->getFirstUnavailable();
    if ($x) {
        $realY = $y + $minY;
        $frequency = ($x * 4000000) + $realY;
        $possible[] = [$frequency, $x, $realY];
        echo "Possible frequency: [x=$x,y=$realY] $frequency\n";
    }
}
echo "Found ", count($possible), " possible frequencies\n";
