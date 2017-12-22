<?php

namespace ES\Bundle\BaseBundle\Translation;

use Symfony\Component\Translation\TranslatorInterface;

class FallbackTranslator implements FallbackTranslatorInterface
{
	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	public function translate(array $keys, array $params = array(), $domain = null, $locale = null)
	{
		while ($key = array_shift($keys)) {
			$translation = $this->translator->trans($key, $params, $domain, $locale);

			if ($translation !== $key) {
				return $translation;
			}
		}

		return $translation;
	}

	public function translateChoice(array $keys, $count, array $params = array(), $domain = null, $locale = null)
	{
		while ($key = array_shift($keys)) {
			$translation = $this->translator->transChoice($key, $count, $params, $domain, $locale);

			if ($translation !== $key) {
				return $translation;
			}
		}

		return $translation;
	}
}