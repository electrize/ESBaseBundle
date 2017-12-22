<?php


namespace ES\Bundle\BaseBundle\Util;

class Javascript
{
	static public function encodeJS($js, $removeNull = false)
	{
		if ($removeNull) {
			foreach ($js as $k => $v) {
				if (null === $v) {
					unset($js[$k]);
				}
			}
		}
		$json = json_encode($js);
		$json = preg_replace_callback('#\"function\s*(?:[a-z_][a-z_0-9]*)?\(((\\\")*|(.*?[^\\\](\\\")*))\"#ius', function ($regs) {
			return substr(str_replace(array("\\n", "\\t"), '', str_replace('\\"', '"', $regs[0])), 1, -1);
		}, $json);

		return $json;
	}
} 