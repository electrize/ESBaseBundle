<?php

namespace ES\Bundle\BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class GMapLocation extends Constraint
{
	public $message = 'You cannot send a message to yourself';

	/**
	 * The minimum precision level of the location
	 * Possible values: country|locality|route|street_number
	 *
	 * @var string
	 */
	public $minLevel;

	public $minLevelMessages = [
		'country'       => 'gmap.location.min_level.country',
		'locality'      => 'gmap.location.min_level.locality',
		'route'         => 'gmap.location.min_level.route',
		'street_number' => 'gmap.location.min_level.street_number',
	];

	/**
	 * {@inheritDoc}
	 */
	public function getTargets()
	{
		return self::PROPERTY_CONSTRAINT;
	}
}
