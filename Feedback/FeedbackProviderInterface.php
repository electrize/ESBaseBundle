<?php


namespace ES\Bundle\BaseBundle\Feedback;

interface FeedbackProviderInterface
{
	public function render(array $options = array());
}