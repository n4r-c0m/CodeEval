<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list( $s1, $s2 ) = explode( ';', trim( $row ) );
	unset($row);
	$m1 = array();
	for ( $i = 0; $i < strlen( $s1 ); $i ++ ) {
		$m1[ $s1[ $i ] ][] = $i;
	}
	$len = array_fill( 0, strlen( $s1 ), 0 );
	$str = array();
	for ( $y = 0; $y < strlen( $s2 ); $y ++ ) {
		$next_len = $next_str = array();
		foreach ( @(array) $m1[ $s2[ $y ] ] as $x ) {
			$next_len[ $x ]   = $x == 0 ? 1 : ( max( array_slice( $len, 0, $x ) ) + 1 );
			$next_str[ $x ]   = @(array) $str[ array_search( $next_len[ $x ] - 1, $len, true ) ];
			$next_str[ $x ][] = $s1[ $x ];
		}
		$len = array_replace( $len, $next_len );
		$str = array_replace( $str, $next_str );
	}

	unset($next_str, $next_len);

	echo implode( '', $str[ array_search( max( $len ), $len, true ) ] ), "\n";
}
?>