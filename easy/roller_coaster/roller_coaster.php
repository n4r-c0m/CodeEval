<?php
$fh = fopen( $argv[1], "r" );
$upcase = false;
while ($ch = fgetc($fh)) {
	$ch = strtolower($ch);
	echo ( $ch < 'a' || $ch > 'z' ) ? $ch : (($upcase = !$upcase) ? strtoupper( $ch ) : $ch);

	if (ord($ch) == 10) {
		$upcase = false;
	}
}