<?php

function printGrid($grid)
{
    foreach ($grid as $row) {
        echo implode('', $row), "\n";
    }
}

function dropSand($grid, $source)
{
    $pos = $source;
    while (true) {
        //try straight down
        $nextPos = [$pos[0], $pos[1] + 1];
        if ($grid[$nextPos[1]][$nextPos[0]] === '.') {
            $pos = $nextPos;
            continue;
        }

        //try diagonal down left
        $nextPos = [$pos[0] - 1, $pos[1] + 1];
        if ($grid[$nextPos[1]][$nextPos[0]] === '.') {
            $pos = $nextPos;
            continue;
        }

        //try diagonal down right
        $nextPos = [$pos[0] + 1, $pos[1] + 1];
        if ($grid[$nextPos[1]][$nextPos[0]] === '.') {
            $pos = $nextPos;
            continue;
        }

        return $pos;
    }
}


$source = [500, 0];
$minX = $source[0];
$maxX = $source[0];
$minY = $source[1];
$maxY = $source[1];
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);
$paths = [];
foreach ($lines as $line) {
    $path = array_map(fn($point) => explode(',', $point), explode(' -> ', $line));
    [$minX, $maxX, $minY, $maxY] = array_reduce($path, function($carry, $item) {
        [$minX, $maxX, $minY, $maxY] = $carry;
        $minX = min($minX, $item[0]);
        $minY = min($minY, $item[1]);
        $maxX = max($maxX, $item[0]);
        $maxY = max($maxY, $item[1]);
        return [$minX, $maxX, $minY, $maxY];
    }, [$minX, $maxX, $minY, $maxY]);
    $paths[] = $path;
}
// add extra padding to the sides and bottom
$minX -= 1;
$maxX += 1;
$maxY += 2;

$grid = [];
$gridSize = [$maxX - $minX + 1, $maxY - $minY + 1];
for ($x=0;$x<$gridSize[0];$x++) {
    for ($y=0;$y<$gridSize[1];$y++) {
        $grid[$y][$x] = '.';
    }
}
$abyss = count($grid) - 2;
$grid[count($grid)-1] = array_fill(0, $gridSize[0], '#');
foreach ($paths as $path) {
    $start = array_shift($path);
    $grid[$start[1] - $minY][$start[0] - $minX] = '#';
    foreach ($path as $point) {
        $step = [$point[0] - $start[0] <=> 0, $point[1] - $start[1] <=> 0];
        $steps = max(abs($point[0] - $start[0]), abs($point[1] - $start[1]));
        for ($i=0; $i<$steps; $i++) {
            $start = [$start[0] + $step[0], $start[1] + $step[1]];
            $grid[$start[1] - $minY][$start[0] - $minX] = '#';
        }
        $start = $point;
    }
}

$sourceNormalized = [$source[0] - $minX, $source[1] - $minY];

$grid[$sourceNormalized[1]][$sourceNormalized[0]] = '+';

$rested = 0;
while (true) {
    $restPos = dropSand($grid, $sourceNormalized);
    $grid[$restPos[1]][$restPos[0]] = 'O';
    if ($restPos[1] === $abyss) {
        break;
    }
    $rested += 1;
}

printGrid($grid);
echo "Units of sand that came to rest before the first hits the abyss: $rested\n";
