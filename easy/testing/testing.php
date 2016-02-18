<?php
$status = array(
	0 => 'Done',
	2 => 'Low',
	4 => 'Medium',
	6 => 'High',
	PHP_INT_MAX => 'Critical'
);

$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	$strings = explode('|', $row);
	$diff = levenshtein(trim($strings[0]), trim($strings[1]));
	echo $status[$diff > 6 ? PHP_INT_MAX : $diff + ($diff % 2)], "\n";
}
?>