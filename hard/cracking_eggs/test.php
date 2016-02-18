<?php
function binomial( $x, $n, $k ) {

	$answer = 0;
	$aux    = 1;

	for ( $i = 1; $i <= $n; $i ++ ) {

		$aux *= $x + 1 - $i;
		$aux /= $i;
		$answer += $aux;

		if ( $answer > $k ) {
			break;
		}
	}

	return $answer;
}

$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list($eggs, $floors) = explode(' ', trim($row));
	$upper = $floors;
	$inf   = 0;
	$mid   = ( $upper + $inf ) / 2;

	while ( $upper - $inf > 1 ) {
		$mid = $inf + ( $upper - $inf ) / 2;
		if ( binomial( $mid, $eggs, $floors ) < $floors ) {
			$inf = $mid;
		} else {
			$upper = $mid;
		}

	}

	echo floor( $inf + 1 ) . " | " . ceil( $inf ) . " | " . round($inf + 1) . " | " . ($inf + 1) . "\n";
}