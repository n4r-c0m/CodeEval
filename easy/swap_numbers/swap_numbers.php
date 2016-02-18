<?php
$fh = fopen( $argv[1], "r" );
while ( $out = preg_replace( '/(\d+)([^\d]+)(\d+)/', '$3$2$1', fgets( $fh ) ) ) {
	echo $out;
}
?>