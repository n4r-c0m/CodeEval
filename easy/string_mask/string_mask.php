<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list($string, $mask) = explode(' ', trim($row));
	$len = strlen($mask);
	for ( $index = 0; $index < $len; $index ++ ) {
		echo chr((ord( $string[ $index ]) - 65) % 32 + 97 - (97 - 65) * $mask[$index]);
	}
	echo "\n";
}
?>