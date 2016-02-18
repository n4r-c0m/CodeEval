<?php
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list($string, $raw_pattern) = explode(',', trim($row));
	unset($row);
	$pattern = '';
	$pattern_asterisks = array();
	for ( $i = 0; $i < strlen( $raw_pattern ); $i ++ ) {
		if ($raw_pattern[$i] === '\\' && $raw_pattern[$i+1] === '*') {
			$pattern = '*';
			$i += 1;
			continue;
		}
		if ($raw_pattern[$i] === '*') {
			$pattern_asterisks[strlen($pattern)] = true;
			continue;
		}

		$pattern .= $raw_pattern[$i];
	}
	unset($raw_pattern, $i);

	$match_start = null;
	$pattern_start = $pattern_index = 0;
	$string_len = strlen($string);
	for ( $i = 0; $i < $string_len; $i ++ ) {
		if (isset($pattern_asterisks[$pattern_index])) {
			$pattern_start = $pattern_index;
			$match_start = null;
			unset( $pattern_asterisks[ $pattern_index ]);
		}
		if ($string[$i] == $pattern[$pattern_index]) {
			if (is_null($match_start)) {
				$match_start = $i;
			}
			$pattern_index++;
		} else {
			if (!is_null($match_start)) {
				$i = $match_start + 1;
				$pattern_index = $pattern_start;
				$match_start = null;
			}
		}

		if ($pattern_index === strlen($pattern)) {
			echo "true\n";
			goto finish;
		}
	}

	echo "false\n";
	finish:
}
