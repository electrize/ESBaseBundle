<?php

namespace ES\Bundle\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NameValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
		if (null !== $value && '' !== $value) {
			if (!preg_match('#^[\w ' . preg_quote($constraint->allowedSpecialChars, '#') . ']+$#usi', $value)) {
				$this->context->addViolation($constraint->message, array('{{ value }}' => $value));
			}
		}
	}
}
