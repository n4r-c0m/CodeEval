<?php
$fh = fopen( $argv[1], "r" );
preg_match_all('/(\d++)\:\s*+\[([^\]]*+)\]/', trim( fgets( $fh ) ), $matches);

$subnets = array();
$hosts = array_combine($matches[1], $matches[2]);
unset($matches);
array_walk($hosts, function(&$value, $host_index) use (&$subnets) {
	if (!$value) {
		$value = array();
		return;
	}
	$list = array();
	foreach ( explode( ',', $value ) as $address ) {
		list($ip, $netmask) = explode('/', trim($address, ' \''));
		$ip_parts = array_map(
			function ( $ip_part, $mask_part, $index ) {
				$i = $ip_part & bindec( $mask_part );

				return $i << ( ( 4 - $index ) * 8 );
			},
			explode( '.', $ip ),
			str_split( str_pad( str_repeat( '1', $netmask ), 8 * 4, '0', STR_PAD_RIGHT ), 8 ),
			range( 1, 4 )
		);
		$list[]   = array_reduce( $ip_parts,
		function($result, $item) {
			return is_null($result) ? $item : $result | $item;
		});

	};

	foreach ( $list as $subnet ) {
		$subnets[$subnet][] = $host_index;
	}

	$value = $list;
});

ksort($subnets);
$routes = array();

while ( $row = fgets( $fh ) ) {
	list($from, $to) = explode(' ', trim($row));
	unset($row);
	list($from, $to) = array((int)$from, (int)$to);

	if ( ! isset( $hosts[ (int) $from ], $hosts[ (int) $to ] ) ) {
		echo "No connection\n";
		continue;
	}

	$to_route_hosts = $from_route_hosts = array();

	$from_route_hosts[] = array($from);
	$from_route_subnets = array($hosts[$from]);

	$to_route_hosts[] = array($to);
	$to_route_subnets = array($hosts[$to]);

	$end_from = false;
	$end_to = false;

	$hosts_count = count( $hosts );
	while ( (!$end_from && !$end_to) && !$middle_subnets = array_intersect( end($to_route_subnets), end($from_route_subnets))) {
		if (count( $from_route_subnets ) > count( $to_route_subnets )) {
			$route_hosts   = &$to_route_hosts;
			$route_subnets = &$to_route_subnets;
			$end           = &$end_to;
		} else {
			$route_hosts   = &$from_route_hosts;
			$route_subnets = &$from_route_subnets;
			$end           = &$end_from;
		}

		append_hop($route_hosts, $route_subnets);
		if (!end($route_subnets)) {
			array_pop($route_subnets);
			array_pop($route_hosts);
			$end = true;
		}
	}

	unset($end_to, $end_from, $end);

	if (!$middle_subnets) {
		echo "No connection\n";
		continue;
	}

	while (!array_intersect($middle_subnets, end($from_route_subnets))) {
		array_pop($from_route_subnets);
		array_pop($from_route_hosts);
	}
	while (!array_intersect($middle_subnets, end($to_route_subnets))) {
		array_pop($to_route_subnets);
		array_pop($to_route_hosts);
	}
	unset($from_route_subnets, $to_route_subnets, $route_subnets);

	$from_route_hosts[] = filter_hosts_by_subnets( array_pop( $from_route_hosts ), $middle_subnets );
	$to_route_hosts[]   = filter_hosts_by_subnets( array_pop( $to_route_hosts ), $middle_subnets );

	$from_route_hosts[] = end( $to_route_hosts );

	$path_from = array_fill_keys( end( $from_route_hosts ), array() );
	$path_to   = array_fill_keys( end( $to_route_hosts ), array() );

	foreach ( $path_from as $host => $value ) {
		$path_from[$host] = &$path_to[$host];
	}
	array_pop( $from_route_hosts );
	array_pop( $to_route_hosts );

	while (($from_route_hosts || $to_route_hosts)) {
		if (count( $from_route_hosts ) < count( $to_route_hosts )) {
			$is_from = false;
			$route_hosts = &$to_route_hosts;
			$path = &$path_to;
		} else {
			$is_from = true;
			$route_hosts = &$from_route_hosts;
			$path = &$path_from;
		};

		$p = array();

		foreach ( $path as $host => $value ) {
			$next_hosts = filter_hosts_by_subnets( end( $route_hosts ), $hosts[ $host ] );
			foreach ( $next_hosts as $next_host ) {
				if ( $is_from ) {
					$p[ $next_host ][ $host ] = &$path[ $host ];
				} else {
					//$path[ $host ][ $next_host ] = array();
					if (isset( $p[ $next_host ])) {
						$path[ $host ][ $next_host ] = &$p[ $next_host ];
					} else {
						$path[ $host ][ $next_host ] = array();
						$p[ $next_host ] = &$path[ $host ][ $next_host ];
					}
				}
			}
		}
		$path = $p;
		array_pop($route_hosts);
		unset($next_hosts, $next_host, $host);
	}
	unset($from_route_hosts, $to_route_hosts, $route_hosts, $path);
	$paths = merge_paths($path_from);
	unset($path_from, $path_to);
	sort($paths);
	foreach ( $paths as &$path ) {
		$path = '[' . implode(', ', $path) . ']';
	}
	unset($path);
	echo implode(', ', $paths) . "\n";
}

function merge_paths($path) {
	$result = array();
	foreach ( $path as $host => $part ) {
		if ($part) {
			foreach ( merge_paths( $part ) as $path_part ) {
				$res      = array_merge( array( $host ), $path_part);
				$result[] = $res;
			}
		} else {
			$result[] = array($host);
		}
	}

	return $result;
}

function get_hosts_by_subnets($search_subnets, $filter_by = array())
{
	global $subnets;

	$found_hosts = array_intersect_key( $subnets, array_flip( $search_subnets ) );
	$found_hosts = (array) @call_user_func_array( 'array_merge', $found_hosts );
	$found_hosts = array_diff( $found_hosts, $filter_by );

	return $found_hosts;
}

function get_subnets_by_hosts($search_hosts, $filter_by = array())
{
	global $hosts, $subnets;

	$found_subnets = array_intersect_key( $hosts, array_flip( $search_hosts ) );
	$found_subnets = (array) @call_user_func_array( 'array_merge', $found_subnets );
	$found_subnets = array_diff( $found_subnets, $filter_by );
	return $found_subnets;
}

function filter_hosts_by_subnets($host_list, $filter_by)
{
	global $hosts, $subnets;

	if (func_num_args() > 2) {
		foreach ( array_slice( func_get_args(), 2 ) as $arg ) {
			if (is_array($arg)) {
				$filter_by = array_merge($arg);
			} else {
				$filter_by[] = $arg;
			}
		}
	}

	$host_list = array_intersect($host_list, call_user_func_array( 'array_merge', array_intersect_key( $subnets, array_flip( $filter_by ) ) ) );
	return $host_list;
}

function append_hop(&$route_hosts, &$route_subnets)
{
	global $hosts, $subnets;

	$next_hosts = array_intersect_key( $subnets, array_flip( end($route_subnets) ) );
	$next_hosts = (array)@call_user_func_array('array_merge', $next_hosts);
	$next_hosts = array_diff( $next_hosts, (array)@call_user_func_array('array_merge', $route_hosts));
	$route_hosts[] = $next_hosts;

	$next_subnets = array_intersect_key($hosts, array_flip( $next_hosts));
	$next_subnets = (array)@call_user_func_array('array_merge', $next_subnets);
	$next_subnets = array_diff($next_subnets, (array)@call_user_func_array('array_merge', $route_subnets));
	$route_subnets[] = $next_subnets;
}