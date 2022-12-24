<?php

class PriorityQueue
{
    protected array $items = [];
    protected array $priorities = [];

    public function add($item, $prio)
    {
        $index = array_search($item, $this->items);
        if ($index !== false) {
            $this->priorities[$index] = $prio;
            return;
        }

        //find where the priority belongs
        $index = 0;
        foreach ($this->priorities as $p) {
            if ($p >= $prio) {
                break;
            }
            $index += 1;
        }

        array_splice($this->priorities, $index, 0, [$prio]);
        array_splice($this->items, $index, 0, [$item]);
    }

    public function shift()
    {
        array_shift($this->priorities);
        $item = array_shift($this->items);
        return $item;
    }
}

function stepBack($grid, $y, $x): array
{
    static $steps = [[0, -1], [0, 1], [-1, 0], [1, 0]];
    $res = [];
    foreach ($steps as [$stepY, $stepX]) {
        $newY = $y + $stepY;
        $newX = $x + $stepX;
        if (isset($grid[$newY][$newX]) && (ord($grid[$y][$x]) - ord($grid[$newY][$newX])) < 2) {
            $res[] = [$newY, $newX];
        }
    }

    //echo "Found ", count($res), " usable neighbours of [$y,$x] ({$grid[$y][$x]}): ", implode(', ', array_map(fn($i) => '[' . implode(',', $i) . ']', $res)), "\n";

    return $res;
}

function distances($grid, $start): array
{
    [$startY, $startX] = $start;

    $dist = [];
    $prev = [];
    $q = new PriorityQueue();
    foreach ($grid as $y => $cols) {
        foreach ($cols as $x => $char) {
            $dist[$y][$x] = PHP_INT_MAX;
            $prev[$y][$x] = null;
            if ($y === $startY && $x == $startX) {
                $dist[$y][$x] = 0;
                $q->add([$y, $x], 0);
            }
        }
    }

    while ([$uy, $ux] = $q->shift()) {
        foreach (stepBack($grid, $uy, $ux) as [$vy, $vx]) {
            $alt = $dist[$uy][$ux] + 1;
            //echo "[$vy, $vx] {$dist[$vy][$vx]} => $alt\n";
            if ($alt < $dist[$vy][$vx]) {
                $dist[$vy][$vx] = $alt;
                $prev[$vy][$vx] = [$uy, $ux];
                $q->add([$vy, $vx], $alt);
            }
        }
    }

    return [$dist, $prev];
}

$possibleStarts = [];
$grid = [];
$start = null;
$end = null;
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);
foreach ($lines as $y => $line) {
    $split = str_split($line, 1);
    foreach ($split as $x => $char) {
        if ($char === 'S') {
            $start = [$y, $x];
            $char = 'a';
        } else if ($char === 'E') {
            $end = [$y, $x];
            $char = 'z';
        }
        $grid[$y][$x] = $char;
        if ($char === 'a') {
            $possibleStarts[] = [$y, $x];
        }
    }
}

$minSteps = PHP_INT_MAX;
$minStart = [-1,-1];
[$dist, $prev] = distances($grid, $end);
foreach ($possibleStarts as [$startY, $startX]) {
    if ($prev[$startY][$startX]) {
        $steps = $dist[$startY][$startX];
        echo "possible start at [$startY, $startX] => $steps\n";
        if ($steps < $minSteps) {
            $minSteps = $steps;
            $minStart = [$startY, $startX];
        }
    }
}

echo "from the ", count($possibleStarts) . " possible starting points it is best to start at ", json_encode($minStart), " this requires $minSteps steps\n";
