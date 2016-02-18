<?php
/*
int InsidePolygon(Point *polygon,int n,Point p)
{
   int i;
   double angle=0;
   Point p1,p2;

   for (i=0;i<n;i++) {
      p1.h = polygon[i].h - p.h;
      p1.v = polygon[i].v - p.v;
      p2.h = polygon[(i+1)%n].h - p.h;
      p2.v = polygon[(i+1)%n].v - p.v;
      angle += Angle2D(p1.h,p1.v,p2.h,p2.v);
   }

   if (ABS(angle) < PI)
      return(FALSE);
   else
      return(TRUE);
}


   Return the angle between two vectors on a plane
   The angle is from vector 1 to vector 2, positive anticlockwise
   The result is between -pi -> pi

double Angle2D(double x1, double y1, double x2, double y2)
{
   double dtheta,theta1,theta2;

   theta1 = atan2(y1,x1);
   theta2 = atan2(y2,x2);
   dtheta = theta2 - theta1;
   while (dtheta > PI)
      dtheta -= TWOPI;
   while (dtheta < -PI)
      dtheta += TWOPI;

   return(dtheta);
}

*/
$fh = fopen( $argv[1], "r" );
while ( $row = fgets( $fh ) ) {
	list( $coords, $p ) = explode( ' | ', trim( $row ) );
	$coords   = explode( ', ', trim( $coords ) );
	$coords[] = $p;
	foreach ( $coords as &$coord ) {
		$coord = explode( ' ', trim( $coord ) );
	}
	unset( $coord );
	$p = array_pop( $coords );

	$count = count( $coords );
	$angle = 0;
	for ( $i = 0; $i < $count; $i ++ ) {
		$a = atan2( $coords[ ( $i + 1 ) % $count ][1] - $p[1], $coords[ ( $i + 1 ) % $count ][0] - $p[0] )
		     - atan2( $coords[ $i ][1] - $p[1], $coords[ $i ][0] - $p[0] );

		while ( $a > pi() ) {
			$a -= 2 * pi();
		}
		while ( $a < - pi() ) {
			$a += 2 * pi();
		}

		$angle += $a;
	}

	echo abs( $angle ) < 2 * pi() ? "Citizen\n" : "Prizoner\n";
}