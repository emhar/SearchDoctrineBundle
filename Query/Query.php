<?php

namespace Emhar\SearchDoctrineBundle\Query;

use Emhar\SearchDoctrineBundle\Mapping\View;
use Emhar\SearchDoctrineBundle\Request\Request;
use Doctrine\ORM\EntityManager;

/**
 * Query build sql and return result
 *
 * @author Emhar
 */
class Query
{

	/**
	 * @var View
	 */
	protected $view;

	/**
	 * @var array 
	 */
	protected $databaseMapping = array();

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * Construct Query
	 *
	 * @param View $view
	 * @param EntityManager $em
	 */
	public function __construct(View $view, EntityManager $em)
	{
		$this->em = $em;
		$this->view = $view;
	}

	/**
	 * Get results for request
	 * 
	 * @param Request $request
	 * @param int $page
	 */
	public function getResults(Request $request, $page)
	{
		$query = $this->buildResultQuery($request->getSearchText(), $request->getLimit(), $request->getLimit() * ($page - 1));
		return $query->getResult();
	}

	/**
	 * Get result count for request
	 *
	 * @param Request $request
	 */
	public function getCount(Request $request)
	{
		$stmt = $this->buildCountQuery($request->getSearchText());
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * @param string $searchText
	 * @param int $limit
	 * @param int $offset
	 * @return \Doctrine\ORM\NativeQuery
	 */
	protected function buildResultQuery($searchText, $limit, $offset)
	{
		$searchWords = preg_split('/[^[:alnum:]]+/', $searchText);
		$searchWordsCount = count($searchWords);
		$selects = array();
		$hitCount = count($this->view->getOrderedRequiredHitNames());
		$scoreConstructPos = $this->view->getScoreConstructPos() !== null ? $this->view->getScoreConstructPos() : $hitCount;
		$typeConstructPos = $this->view->getTypeConstructPos() !== null ? $this->view->getTypeConstructPos() : $hitCount + 1;
		foreach ($this->view->getDatabaseMapping() as $tableMapping)
		{
			$scores = $this->buidScore($tableMapping['scoreMappings'], $searchWordsCount);
			$tableMapping['hits'][$scoreConstructPos] = implode($scores, '+') . ' AS c' . $scoreConstructPos;
			$tableMapping['hits'][$typeConstructPos] = '\'' . $tableMapping['entityName'] . '\' AS c' . $typeConstructPos;

			//For constructor parameter order of searchitem
			ksort($tableMapping['hits']);

			$selects[] = 'SELECT ' . implode($tableMapping['hits'], ', ') . ' '
					. 'FROM ' . $tableMapping['name'] . ' '
					. implode(' ', $tableMapping['joins']) . ' '
					. 'HAVING c' . $scoreConstructPos . ' <> 0 '
					. 'ORDER BY c' . $scoreConstructPos . ' DESC '
					. 'LIMIT ' . ($offset + $limit)
			;
		}

		$bigSelect = '(' . implode($selects, ') UNION (') . ') '
				. 'ORDER BY c' . $scoreConstructPos . ' DESC '
				. 'LIMIT ' . $limit . ' '
				. 'OFFSET ' . $offset
		;

		$query = $this->em->createNativeQuery($bigSelect, $this->view->getRsm());
		foreach ($searchWords as $key => $searchWord)
		{
			$query->setParameter('p' . $key, $searchWord);
		}

		return $query;
	}

	/**
	 * @param string $searchText
	 * @return \Doctrine\DBAL\Statement;
	 */
	protected function buildCountQuery($searchText)
	{
		$searchWords = preg_split('/[^[:alnum:]]+/', $searchText);
		$searchWordsCount = count($searchWords);
		$selects = array();
		foreach ($this->view->getDatabaseMapping() as $tableMapping)
		{
			$scores = $this->buidScore($tableMapping['scoreMappings'], $searchWordsCount);

			$selects[] = 'SELECT count(*) '
					. 'FROM ' . $tableMapping['name'] . ' '
					. implode(' ', $tableMapping['joins']) . ' '
					. 'WHERE ' . implode($scores, '+') . ' <> 0 '
			;
		}

		$bigSelect = 'SELECT (' . implode(')+(', $selects) . ')';

		$indexedSearchWords = $searchWords;
		array_walk($indexedSearchWords, function($value, &$key)
		{
			$key = ":p" . $key;
		});
		$stmt = $this->em->getConnection()->prepare($bigSelect);
		foreach ($searchWords as $key => $searchWord)
		{
			$stmt->bindParam('p' . $key, $searchWord);
		}

		return $stmt;
	}

	protected function buidScore($scoreMappings, $searchWordsCount)
	{
		$scores = array();
		foreach ($scoreMappings as $scoreMapping)
		{
			$score = 'IFNULL(' . $scoreMapping['rankFactor'] . '*(LENGTH(' . $scoreMapping['string'] . ')'
					. '-LENGTH('
					. str_repeat('REPLACE(', $searchWordsCount)
					. 'LOWER(' . $scoreMapping['string'] . ')';
			for ($i = 0; $i < $searchWordsCount; $i++)
			{
				$score .= ', LOWER(:p' . $i . '), \'\')';
			}
			$score .= ')),0)';
			$scores[] = $score;
		}
		return $scores;
	}

}
