<?php
  // THIS CONVERTER AT THE MOMENT ONLY CONVERTS SIMPLE SVG PATH TO WKT
  // OTHER CONVERSIONS MIGHT FOLLOW IN FUTURE IF ANY REQUIREMENT COMES INTO PICTURE
  
  // remove warnings
  error_reporting(E_ERROR | E_PARSE);

  // Point Class - Defines x & y coordinates of a point in space
  // Also as pre defined functions to determine the Path length & quadrativ bezier curve length
  class Point {
    private $x;
    private $y;

    function __construct($x, $y) {
      $this->x = $x;
      $this->y = $y;
    }

    public function getX() {
      return $this->x;
    }

    public function getY() {
      return $this->y;
    }

    public function lineLength($p) {
      $x1 = $this->x;
      $y1 = $this->y;

      $x2 = $p->getX();
      $y2 = $p->getY();

      $diff_x = $x2 - $x1;
      $diff_y = $y2 - $y1;

      return sqrt(pow($diff_x, 2) + pow($diff_y, 2));
    }

    public function curveLength($p1, $p2) {
      $ax = $this->x - 2 * $p1->getX() + $p2->getX();
      $ay = $this->y - 2 * $p1->getY() + $p2->getY();
      $bx = 2 * $p1->getX() - 2 * $this->x;
      $by = 2 * $p1->getY() - 2 * $this->y;
      $a = 4 * (pow($ax, 2) + pow($ay, 2));
      $b = 4 * ($ax * $bx + $ay * $by);
      $c = pow($bx, 2) + pow($by, 2);

      $sabc = 2 * sqrt($a + $b + $c);
      $a_2 = sqrt($a);
      $a_32 = 2 * $a * $a_2;
      $c_2 = 2 * sqrt($c);
      $ba = $b / $a_2;

      return ($a_32 * $sabc + $a_2 * $b * ($sabc - $c_2) + (4 * $c * $a - $b * $b) * log((2 * $a_2 + $ba + $sabc) / ($ba + $c_2))) / (4 * $a_32);
    }
  }

  // SVG to WKT Transformer Class
  class SVGtoWKT {
    private $TOTAL_PATH_LENGTH = 0.0;
    private $POINTS = array();
    private $XMIN = 99999999, $YMIN = 99999999, $XMAX = -99999999, $YMAX = -99999999;
    private $WKT = "";
    private $RESULT_ARR = array();

    // function to determine the min/max x/y points and also push the point to the POINTS array
    private function pushToPoints($point) {
      $this->XMIN = min($this->XMIN, $point->getX());
      $this->YMIN = min($this->YMIN, $point->getY());
      $this->XMAX = max($this->XMAX, $point->getX());
      $this->YMAX = max($this->YMAX, $point->getY());

      array_push($this->POINTS, $point);
    }

    public function getXMin() {
      return $this->XMIN;
    }

    public function getXMax() {
      return $this->XMAX;
    }

    public function getYMin() {
      return $this->YMIN;
    }

    public function getYMax() {
      return $this->YMAX;
    }

    public function getWKT() {
      return $this->WKT;
    }

    // function to return x/y min/max & wkt transformation in the form of array
    // [0] -> x min
    // [1] -> y min
    // [2] -> x max
    // [3] -> y max
    // [4] -> WKT representation
    public function getResult() {
      array_push($this->RESULT_ARR, $this->XMIN);
      array_push($this->RESULT_ARR, $this->YMIN);
      array_push($this->RESULT_ARR, $this->XMAX);
      array_push($this->RESULT_ARR, $this->YMAX);
      array_push($this->RESULT_ARR, $this->WKT);

      return $this->RESULT_ARR;
    }

    // function to calculate points on a straight line b/w 2 given points
    // As $t increases from 0 to 1, the curve departs from $start_point in the direction of $end_point
    public function linearBezier($start_point, $end_point, $t) {
      while($t < 1) {
        $x = (1 - $t) * $start_point->getX() + $t * $end_point->getX();
        $y = (1 - $t) * $start_point->getY() + $t * $end_point->getY();

        $this->pushToPoints(new Point($x, $y * -1));
        $t += $t;
      }
    }

    // function to calculate points on a curve b/w 2 given points
    // As $t increases from 0 to 1, the curve departs from $start_point in the direction of $end_point controlled by $control_point
    public function quadBezier($start_point, $control_point, $end_point, $t) {
      while($t < 1) {
        $x = $start_point->getX() * pow(1 - $t, 2) + 2 * (1 - $t) * $t * $control_point->getX() + $end_point->getX() * pow($t, 2);
        $y = $start_point->getY() * pow(1 - $t, 2) + 2 * (1 - $t) * $t * $control_point->getY() + $end_point->getY() * pow($t, 2);

        $this->pushToPoints(new Point($x, $y * -1));
        $t += $t;
      }
    }

    // call this function to convert from svg to wkt
    // pass d attribute info to this function
    public function convertPath($d) {
      // check for empty or null $d
      if(!isset($d) || strlen(trim($d)) == 0) {
        throw new Exception("Empty Path.");
      }
      
      $arr = preg_split( "/(M|L|Q)/", $d, 0, PREG_SPLIT_DELIM_CAPTURE);
      $prev_point = null;
      $command = '';
      
      // calculate total path length
      foreach($arr as $path) {
        $path = trim($path);
        if(strlen($path) == 0) {
          continue;
        }

        if(strlen($path) == 1) {
          $command = $path;
        } else {
          if($command == 'M') { // Move To
            $split = explode(' ', $path);
            $x = floatval($split[0]);
            $y = floatval($split[1]);

            $prev_point = new Point($x, $y);
            $this->pushToPoints(new Point($x, -1 * $y));
          } else if($command == 'L') { // Draw Line
            $split = explode(' ', $path);
            $x = floatval($split[0]);
            $y = floatval($split[1]);

            $end_point = new Point($x, $y);

            $length = $prev_point->lineLength($end_point);
            $this->TOTAL_PATH_LENGTH += $length;
            
            $this->linearBezier($prev_point, $end_point, 1 / (ceil($length) + 1));
            $prev_point = new Point($x, $y);
          } else if($command == 'Q') { // Quadratic Belzier Curve
            $split = explode(' ', $path);
            $c_x = floatval($split[0]);
            $c_y = floatval($split[1]);
            $x = floatval($split[2]);
            $y = floatval($split[3]);

            $control_point = new Point($c_x, $c_y);
            $end_point = new Point($x, $y);

            $length = $prev_point->curveLength($control_point, $end_point);
            $this->TOTAL_PATH_LENGTH += $length;

            $this->quadBezier($prev_point, $control_point, $end_point, 1 / (ceil($length) + 1));
            $prev_point = $end_point;
          }
        }
      }

      // push last point
      $this->pushToPoints(new Point($prev_point->getX(), -1 * $prev_point->getY()));

      // prepare wkt
      $this->WKT = "GEOMETRYCOLLECTION(LINESTRING(";
      foreach($this->POINTS as $point) {
        $x = $point->getX();
        $y = $point->getY();

        $this->WKT .= $x . " " . $y . ",";
      }
      $this->WKT = rtrim($this->WKT, ",");
      $this->WKT .= "))";
    }
  }

  