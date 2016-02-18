<?php
$fh = fopen( $argv[1], "r" );
$count_all = $count_lower = $count_upper = 0;
while ( !feof($fh) ) {
	$ch = fgetc( $fh );

	if (ord($ch) == 10 || feof( $fh )) {
		printf("lowercase: %01.2f uppercase: %01.2f\n", $count_lower * 100 / $count_all, $count_upper * 100 / $count_all);
		$count_all = $count_lower = $count_upper = 0;
		continue;
	}

	$count_all++;
	if (strtolower($ch) === $ch) {
		$count_lower++;
	} else {
		$count_upper++;
	}
}
?>