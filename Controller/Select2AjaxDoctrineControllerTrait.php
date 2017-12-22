<?php

namespace ES\Bundle\BaseBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait Select2AjaxDoctrineControllerTrait
{
	protected function select2DoctrineAjaxAction($class, \Closure $map, array $fields = ['name'], \Closure $queryFilter = null, $limit = 10)
	{
		$request = $this->get('request');
		$query   = $request->query->get('query');
		$id      = $request->query->get('id');

		$repo         = $this->get('doctrine')->getRepository($class);
		$queryBuilder = $repo->createQueryBuilder('t');
		if ($id) {
			$queryBuilder->andWhere('t.id IN (:id)')
				->setParameter('id', explode(',', $id));
		} elseif (count($fields) > 0) {
			foreach ($fields as $field) {
				$queryBuilder->orWhere('t.' . $field . ' LIKE :' . $field)
					->setParameter($field, '%' . str_replace(' ', '%', $query) . '%');
			}
		}
		if (null !== $queryFilter) {
			$queryFilter($queryBuilder);
		}
		$queryBuilder->setMaxResults($limit);
		$results = $queryBuilder->getQuery()->getResult();

		$data = [];
		foreach ($results as $result) {
			$data[] = $map($result);
		}

		return new JsonResponse($data);
	}
} 