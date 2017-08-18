<?php 

namespace App\Helper;

class RSA {
	public $e = 2111;
	public $d = 1896071860031;
	public $n = 3243609082547;

	public function generateKey($p = 704603, $q = 3530867) {
		ini_set("precision", 1000000);

		$n = $p * $q;

		$ole = ($p - 1) * ($q - 1);

		do {
			$e = rand(50, 10000);
		} while ($this->NTCNDQ($e, $ole) != 1);

		
		$b = $this->modulur($e, $ole);

		$d = $ole + $b;

		$this->e = $e;
		$this->d = $d;
		$this->n = $n;

		return true;
	}

	public function encode($m) {
		ini_set("precision", 1000000);
		$c = bcpowmod($m, $this->e, $this->n);

		return $c;
	}

	public function decode($c) {
		ini_set("precision", 1000000);
		$m = bcpowmod($c, $this->d, $this->n);
		
		return $m;
	}

	private function getModulus($n, $m) {
	    $a = str_split($n);
	    $r = 0;

	    foreach($a as $v)
	    {
	        $r = ((($r * 10) + intval($v)) % $m);
	    }

	    return $r;
	}

	private function NTCNDQ($a, $b) {
	    if($b == 0)
	        return ($a == 1);
	    return $this->NTCNDQ($b, $a % $b);
	}

	private function modulur($a, $m) {
		$y0 = 0;
		$y1 = 1;
		$y = 0;
		do {
			$r = bcmod($m, $a);

			if ($r == 0) break;

			$q = bcdiv($m, $a);

			$y = $y0 - $y1 * $q;

			$y0 = $y1;
			$y1 = $y;
			$m = $a;
			$a = $r;
		} while($a > 0);

		return $y;
	}
}