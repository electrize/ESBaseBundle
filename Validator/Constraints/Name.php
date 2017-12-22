<?php


namespace ES\Bundle\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @api
 */
class Name extends Constraint
{
	public $message = 'name.invalid';

	public $allowedSpecialChars = '-\'’';
} 