<?php

namespace ES\BaseBundle\Util;

class Url
{
	static public function fixScheme($str)
	{
		return preg_replace('#\=("|\')(http)\:\/\/#u', '=$1//', $str);
	}

	static public function urlize($str)
	{
		$str = preg_replace('/(^|\s)((?:http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\<\s]*)?)/u', '\\1<a href="\\2" target="_blank">\\2</a>', $str);
		$str = preg_replace('/(^|\s)(www\.[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\<\s]*)?)/u', '\\1<a href="http://\\2" target="_blank">\\2</a>', $str);

		return $str;
	}
} 