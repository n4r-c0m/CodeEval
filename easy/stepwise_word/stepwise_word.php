<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	$words = explode( ' ', trim($row) );
	$lengths = array_map('strlen', $words);
	$word = $words[ array_search( max( $lengths ), $lengths, true )];
	unset($words, $lengths);
	foreach ( str_split( $word ) as $index => $char ) {
		echo str_repeat('*', $index), $char, ' ';
	}
	echo "\n";
}
?>