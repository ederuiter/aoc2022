<?php

function getDuplicate($line) {
  $items = str_split($line, 1);
  [$part1, $part2] = array_chunk($items, ceil(count($items) / 2));
  $res = array_values(array_intersect($part1, $part2))[0] ?? '';
  
  echo implode('', $part1) . ' ' . implode('', $part2) . ' => ' . $res, "\n";
  return $res;
}

function getBadge($group) {
  $splitted = [];
  foreach ($group as $member) {
    $splitted[] = str_split($member, 1);
  }

  $res = array_values(array_intersect(...$splitted))[0] ?? '';
  echo implode(' ', $group) . ' => ' . $res, "\n";
  return $res;
}

$priority = array_flip([
  '', 
  'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
  'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
]);

$priorities = 0;
$lines = file("input.txt", FILE_IGNORE_NEW_LINES);
$groups = array_chunk($lines, 3);
foreach ($groups as $group) {
   $priorities += $priority[getBadge($group)];
}
echo $priorities, "\n";
