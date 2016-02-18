<?php
$fh = fopen( $argv[1], "r" );
while ( !feof($fh) ) {
	$stack  = array( 1, 1 );

	$number = fgets( $fh );
	if ( trim( $number ) === '0' ) {
		echo "0\n";
	}

	if ( ! $number = (int) $number ) {
		continue;
	}

	for ( $number -= 1; $number > 0; $number -- ) {
		$stack[] = array_sum( $stack );
		array_shift( $stack );
	}

	echo number_format( end( $stack ), 0, '', '' ), "\n";
}