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
$correct = 0;
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);
foreach ($lines as $y => $line) {
    if (strlen($line) > 0) {
        $packets[] = json_decode($line);
    }
    if (count($packets) == 2) {
        $index += 1;
        if (compareOrdering($packets[0], $packets[1]) <= 0) {
            echo "correct:\n";
            $correct += $index;
        } else {
            echo "incorrect:\n";
        }
        echo "=> ", json_encode($packets[0]), "\n";
        echo "=> ", json_encode($packets[1]), "\n";
        $packets = [];
    }
}

echo $correct, "\n";
