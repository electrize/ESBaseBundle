<?php


namespace ES\Bundle\BaseBundle\Util;

class UniqueCode
{
	/**
	 * @param int $id
	 * @return string
	 */
	static public function getCodeFromId($id)
	{
		$id = (int)$id;

		$offsets = array(
			'WS7UPJFRYDHM1CTZA49K6B8LIO23VEX0NQ5G',
			'1XW6O5N3TFBZSDCAP87LY9QHGJVRI0E4KM2U',
			'JV2QZNXB18O6YDWH0I4PE9RGACSTF5M37UKL',
			'8EMD3I1OCA2WGJQ6UZYRVPK40T975NXHLBSF',
			'4JRC57T8VW1FOLYU0QXDZPK6SEM3NABHI2G9',
			'OPRB5GUFKQ8V4CM21A6W9ZISTXYDNHL37J0E',
		);

		$base   = 35;
		$code   = array();
		$length = count($offsets);
		for ($i = 1; $id >= 0 && $i <= $length; $i++) {
			$x      = (int)($id % pow($base, $i) / pow($base, $i - 1));
			$code[] = $offsets[$i - 1][$x + ($i > 1 ? 1 : 0)];
			$id -= pow($base, $i);
		}
		for ($i = count($code) + 1; $i <= $length; $i++) {
			$code[] = $offsets[$i - 1][0];
		}

		return implode('', $code);
	}
} 