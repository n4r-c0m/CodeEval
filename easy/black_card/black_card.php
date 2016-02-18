<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list($names, $number) = explode('|', trim($row));
	$number = (int)$number;
	unset($row);
	$names = explode(' ', trim($names));

	while ( count( $names ) > 1) {
		unset($names[ ( $number - 1 ) % count( $names ) ]);
		$names = array_values($names);
	}

	echo reset($names) . "\n";
}