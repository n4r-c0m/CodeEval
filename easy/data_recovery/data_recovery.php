<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list($words, $hint) = explode(';', trim($row));
	$words = explode(' ', $words);
	$hint = explode(' ', $hint);
	$hint = array_merge($hint, array_diff( range( 1, count( $words ) ), $hint ));
	$words = array_combine($hint, $words);
	ksort($words);
	echo implode(' ', $words), "\n";
}
?>