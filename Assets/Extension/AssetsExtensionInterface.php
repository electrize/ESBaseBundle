<?php


namespace ES\Bundle\BaseBundle\Assets\Extension;

interface AssetsExtensionInterface
{
	function getJavascriptIncludes();

	function getCSSIncludes();

	function getJavascriptCode();
} 