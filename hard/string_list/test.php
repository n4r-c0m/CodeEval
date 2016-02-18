<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list($n, $chars) = explode(',', trim($row));
	$n = (int)$n;
	$chars = array_unique( str_split( $chars ) );
	sort($chars);
	$c = pow(count($chars), $n);
	for ($i = 0; $i < $c; $i++) {
		$ii = $i;
		$word = array();
		for ($j = 1; $j <= $n; $j++) {
			$word[] = $chars[$ii % count($chars)];
			$ii = (int)floor( $ii / count( $chars ));
		}
		echo implode('', array_reverse($word)), $i + 1 < $c ? ',' : '';
		unset($word);
	}
	echo "\n";
}