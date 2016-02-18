<?php
function get_permutations($ar)
{
    $result = array();
    if (count($ar) == 1) {
        return $ar;
    }

    foreach ($ar as $index => $char) {
        $ar2 = $ar;
        unset($ar2[$index]);
        foreach (get_permutations($ar2) as $permutation) {
            $result[] = $char . $permutation;
        }
    }

    return $result;
}

$fh = fopen($argv[1], "r");
while ($row = fgets($fh)) {
    $row = str_split(rtrim($row));
    sort($row);
    echo implode(',', get_permutations($row)), "\n";
}
?>