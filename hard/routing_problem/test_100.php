<?php
$fh = fopen( $argv[1], "r" );
preg_match_all('/(\d++)\:\s*+\[([^\]]*+)\]/', trim( fgets( $fh ) ), $matches);

$subnets = array();
$hosts = array_combine($matches[1], $matches[2]);
array_walk($hosts, function(&$value, $host_index) use (&$subnets) {
	$list = array();
	if (!$value) {
		return array();
	}
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

	if ( ! isset( $hosts[ (int) $from ], $hosts[ (int) $to ] ) ) {
		echo "No connection\n";
		continue;
	}

	$route_hosts = array();
	$route_subnets = array();
	$new_routes = array();
	$used_hosts = array((int)$to);

	$route_subnets[] = (array)@$hosts[(int)$to];
	$new_routes = $route_subnets[0];

	while ( count( $used_hosts ) !== count( $hosts ) && !array_intersect( (array)@$hosts[ (int)$from ], $new_routes ) && $new_routes ) {
		$interm_hosts = (array)array_intersect_key( $subnets, array_flip( $new_routes ) );
		$interm_hosts[] = array();
		$interm_hosts = (array)call_user_func_array( 'array_merge', $interm_hosts );
		$interm_hosts = (array)array_diff( array_unique( $interm_hosts ), $used_hosts );
		$used_hosts   = (array)array_merge( $used_hosts, $interm_hosts );
		$route_hosts[] = $interm_hosts;

		$new_routes = array();
		$new_routes = (array)array_intersect_key($hosts, array_flip($interm_hosts));
		$new_routes[] = array();
		$new_routes = (array)call_user_func_array( 'array_merge', $new_routes );
		$new_routes = (array)array_diff($new_routes, call_user_func_array( 'array_merge', $route_subnets ));
		$route_subnets[] = $new_routes;
	}

	if ( ! array_intersect( (array)$hosts[ $from ], $new_routes )) {
		echo "No connection\n";
		continue;
	}

	$route_hosts = array_reverse( $route_hosts);
	$route_subnets = array_reverse( $route_subnets);
	$paths = find_paths((int)$from, (int)$to, $route_subnets, $route_hosts);
	usort($paths, function($a, $b) {
		return $a < $b ? -1 : 1;
	});
	if ( ! is_array( reset( $paths ) ) ) {
		$paths = array( $paths );
	}
	foreach ( $paths as &$path ) {
		$path = '[' . implode(', ', $path) . ']';
	}
	unset($path);
	echo implode(', ', $paths) . "\n";
}

function find_paths($from, $to, $via_subnets, $via_hosts, $current_path = array()) {
	$current_path[] = $from;
	global $hosts, $subnets;
	$inter_subnets = array_intersect($via_subnets[0], (array)@$hosts[$from]);
	$next_hosts = (array)array_intersect_key( $subnets, array_flip( $inter_subnets ) );
	$next_hosts[] = array();
	$next_hosts = call_user_func_array('array_merge', $next_hosts);

	if (in_array($to, $next_hosts)) {
		$current_path[] = $to;
		return $current_path;
	}
	$next_hosts = array_intersect( $next_hosts, $via_hosts[0] );
	if (!$next_hosts) {
		return array();
	}

	//$next_hosts = array_intersect($next_hosts, call_user_func_array('array_merge', array_intersect_key($subnets, array_flip($via_subnets[1]))));
	$result = array();
	foreach ( $next_hosts as $next_host ) {
		$path = find_paths($next_host, $to, (array)@array_slice($via_subnets, 1), (array)@array_slice($via_hosts, 1), $current_path);
		if ($path) {
			$result = array_merge($result, is_array($path[0]) ? $path : array($path));
		}
	}

	return isset($result[1]) || count($current_path) == 1 ? $result : $result[0];
}