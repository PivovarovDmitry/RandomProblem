<?php
function sum($a, $b) {
	if (is_numeric($a) && is_numeric($b)) 
    return $a+$b;
  else
    return '('.$a.'+'.$b.')';
}

function frac($a,$b) {
	if (is_numeric($a) && is_numeric($b)) { 
		if($b==0)
			return "\\varnothing";
		else {
			if (($a<0 && $b<0) || ($a>0 && $b>0)) {
				$sign = "";
        $sign_num = 1;
      }
			else {
				$sign = "-";
        $sign_num = -1;
      }
			$res = abs($a) % abs($b);
			$int = floor (abs($a) / abs($b));
			if ($res == 0)
				return $sign_num*$int;
			else {
				if ($int == 0) 
					$int = "";
				return "$sign$int\\frac{".abs($res)."}{".abs($b)."}";
			}
		}
	}
	else
		return "\\frac{ $a }{ $b }";
}

function sq($a) {
	return "\\sqrt{ $a }";
}
function arccos($a) {
	return "\\arccos{ $a }";
}
function arcsin($a) {
	return "\\arcsin{ $a }";
}
function plane($a,$b,$c,$d) {
	$signb = $b<0 ? "-" : "+";
	$signc = $c<0 ? "-" : "+";
	$signd = $d<0 ? "-" : "+";
	$b = abs($b);
	$c = abs($c);
	$d = abs($d);
	if ($b==1) $b="";
	if ($c==1) $c="";
	return "$a x $signb $b y $signc $c z $signd $d = 0";
}
function point($a,$b,$c) {
	return "($a; $b; $c)";
}
function lkv($ka,$a,$kb,$b,$kc,$c) {
	if (is_numeric($kb)) {
		$signkb = $kb<0 ? "-" : "+";
		$kb = abs($kb);
	}
	else
		$signkb = "+";
	if (is_numeric($kc)) {
		$signkc = $kc<0 ? "-" : "+";
		$kc = abs($kc);
	}
	else
		$signkc = "+";
	return "$ka $a $signkb $kb $b $signkc $kc $c";
}
function set(...$args) {
	$index = array_rand($args);
	return $args[$index];
}

?>
