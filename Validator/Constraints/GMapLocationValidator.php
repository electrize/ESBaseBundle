<?php

namespace ES\Bundle\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GMapLocationValidator extends ConstraintValidator
{
	private $fieldPriority = [
		'country',
		'locality',
		'route',
		'street_number',
	];

	private $fieldMapping = [
		'country'       => 'country',
		'locality'      => 'locality',
		'route'         => 'route',
		'street_number' => 'street_number',
	];

	/**
	 * Indicates whether the constraint is valid
	 *
	 * @param array      $location
	 * @param Constraint $constraint
	 */
	public function validate($location, Constraint $constraint)
	{
		$minLevel = $constraint->minLevel;

		if (!is_array($location) || !$location['latitude']) {
			return;
		}

		if (!isset($this->fieldMapping[$constraint->minLevel])) {
			throw new \InvalidArgumentException(sprintf('Invalid level "%s". Available levels are "%s"',
				$constraint->minLevel,
				implode('", "', array_keys($this->fieldMapping))
			));
		}

		$requirement = $this->fieldMapping[$constraint->minLevel];

		$priorities = $this->fieldPriority;
		foreach ($priorities as $field) {
			if (!array_key_exists($field, $location)) {
				throw new \InvalidArgumentException(sprintf('Missing "%s" key on location.', $field));
			}
			if (!$location[$field]) {
				$this->context->addViolation($constraint->minLevelMessages[$field], ['%level%' => $field]);
				return;
			}

			// We arrive at the end of the min level requirement
			if ($field === $requirement) {
				return;
			}
		}
	}
}
