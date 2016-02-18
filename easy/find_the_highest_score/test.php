<?php
function _explode($value) {
	return explode( ' ', $value );
}

$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	$lists = explode( ' | ', trim( $row ) );
	$lists = array_map( '_explode', $lists );
	$score = call_user_func_array( 'array_map', array( - 1 => 'max' ) + $lists );
	echo implode( ' ', $score ), "\n";
}
?>

