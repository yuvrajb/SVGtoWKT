# SVGtoWKT
SVG to WKT Converter in PHP

At the moment it converts SVG Path containing only M, multiple L & Q commands to GWT. I might add more if requirement comes into picture. Feel free to develop on top of this.

# Usage
```sh
import './SVGtoWKT.php';

$obj = new SVGtoWKT();
try {
    $obj->convertPath("M 95.374443 88.915543 L 95.224091 88.464470 Q 95.073730 88.013397 94.852547 87.614311 Q 94.631355 87.215218 94.170654 86.912781 Q 93.709938 86.610344 92.892426 86.497040 Q 92.074913 86.383743 90.877228 86.674057 Q 89.679527 86.964371 88.426033 87.988846 Q 87.172546 89.013329 86.268349 90.631721 Q 85.364151 92.250107 84.954124 94.070526 Q 84.544113 95.890938 84.748436 97.544434 Q 84.952759 99.197922 85.756165 100.407608 Q 86.559570 101.617287 87.855309 102.359085 Q 89.151039 103.100876 90.771538 103.332840 Q 92.392036 103.564812 93.926155 103.225479 Q 95.460289 102.886139 96.795532 102.036346 Q 98.130768 101.186539 99.102737 100.089798 Q 100.074692 98.993057 100.641228 97.823112 Q 101.207779 96.653168 101.475052 95.272293 Q 101.742340 93.891426 101.569084 92.351707 Q 101.395836 90.811974 100.679527 89.420235 Q 99.963211 88.028496 98.888687 87.004745 Q 97.814163 85.980995 96.170876 85.362961 Q 94.527596 84.744926 92.785896 84.965820 L 91.044182 85.186707");

    echo $obj->getXMin() . "<br/>";
    echo $obj->getYMin() . "<br/>";
    echo $obj->getXMax() . "<br/>";
    echo $obj->getYMax() . "<br/>";
    echo $obj->getWKT() . "<br/>";
    print_r($obj->getResult());
    
} catch(Exception $ex) {
    echo "Error: " . $ex->getMessage();
}
```

# Resources
* [Quadratic Bézier Curve Length](https://gist.github.com/tunght13488/6744e77c242cc7a94859) - Converted the JS formula for calculating curve length to a PHP function
* [Bézier Curves - Wikipedia](https://en.wikipedia.org/wiki/B%C3%A9zier_curve#Linear_B%C3%A9zier_curves) - Used the formula mentioned under Specific cases for calculating location of points for values of 0<=t<=1 for both linear & quadratic Bézier curves

# License
MIT
