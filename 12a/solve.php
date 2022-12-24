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

function neighbours($grid, $y, $x): array
{
    static $steps = [[0, -1], [0, 1], [-1, 0], [1, 0]];
    $res = [];
    foreach ($steps as [$stepY, $stepX]) {
        $newY = $y + $stepY;
        $newX = $x + $stepX;
        if (isset($grid[$newY][$newX]) && (ord($grid[$newY][$newX]) - ord($grid[$y][$x])) < 2) {
            $res[] = [$newY, $newX];
        }
    }

    //echo "Found ", count($res), " usable neighbours of [$y,$x] ({$grid[$y][$x]}): ", implode(', ', array_map(fn($i) => '[' . implode(',', $i) . ']', $res)), "\n";

    return $res;
}

function shortestPath($grid, $start, $end): array
{
    [$startY, $startX] = $start;
    [$endY, $endX] = $end;

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
        if ($uy === $endY && $ux === $endX) {
            $path = [];
            $current = $end;
            while ($current) {
                array_unshift($path, $current);
                $current = $prev[$current[0]][$current[1]];
            }
            return $path;
        }

        foreach (neighbours($grid, $uy, $ux) as [$vy, $vx]) {
            $alt = $dist[$uy][$ux] + 1;
            //echo "[$vy, $vx] {$dist[$vy][$vx]} => $alt\n";
            if ($alt < $dist[$vy][$vx]) {
                $dist[$vy][$vx] = $alt;
                $prev[$vy][$vx] = [$uy, $ux];
                $q->add([$vy, $vx], $alt);
            }
        }
    }

//    print_r($dist);
//    print_r($prev);

    return [];
}

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
    }
}

$path = shortestPath($grid, $start, $end);
if ($path) {
    echo "path found; minimal number of steps: ", count($path) -1, "\n";
}

