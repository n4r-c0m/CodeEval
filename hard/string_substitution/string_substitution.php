<?php
$fh = fopen($argv[1], "r");
while ($row = fgets($fh)) {
    list ($s, $reps) = explode(';', rtrim($row));
    unset($row);
    $reps = explode(',', $reps);
    $injects = array();
    $reps_count = count($reps);
    for ($i = 0; $i < $reps_count; $i += 2) {
        $o = 0;
        while (false !== $o = strpos($s, $reps[$i], $o)) {
            $s = substr($s, 0, $o) . str_pad('%', strlen($reps[$i])) . substr($s, $o + strlen($reps[$i]));
            $injects[$o] = $reps[$i + 1];
        }
    }
    ksort($injects);
    echo vsprintf(str_replace(array('%', ' '), array('%s', ''), $s), $injects), "\n";
}