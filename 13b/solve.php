<?php

function compareOrdering($packet1, $packet2): int
{
    if (is_integer($packet1) && is_integer($packet2)) {
        return $packet1 <=> $packet2;
    } elseif (is_array($packet1) && is_array($packet2)) {
        foreach ($packet1 as $index => $part1) {
            if (!isset($packet2[$index])) {
                return 1;
            }
            $partOrder = compareOrdering($part1, $packet2[$index]);
            if ($partOrder != 0) {
                return $partOrder;
            }
        }
        if (count($packet2) > count($packet1)) {
            return -1;
        }
        return 0;
    } else {
        return compareOrdering((array) $packet1, (array) $packet2);
    }
}

$index = 0;
$packets = [];
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);
foreach ($lines as $y => $line) {
    if (strlen($line) > 0) {
        $packets[] = json_decode($line);
    }
}

$packets[] = [[2]];
$packets[] = [[6]];

usort($packets, 'compareOrdering');

$result = 1;
$key = 1;
foreach ($packets as $packet) {
    $index += 1;
    $p = json_encode($packet);
    if ($p === '[[2]]' || $p === '[[6]]') {
        echo $p, " => ", $index, "\n";
        $key = $key * $index;
    }
}
echo "Decoder key is: $key\n";
