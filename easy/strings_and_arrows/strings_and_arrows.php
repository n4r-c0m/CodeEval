<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	$count = 0;
	foreach ( array( '>>-->', '<--<<' ) as $arrow ) {
		$pos = -1;
		while (false !== ($pos = strpos($row, $arrow, $pos+1))) {
			$count++;
		}
	}

	echo $count, "\n";
}
?>