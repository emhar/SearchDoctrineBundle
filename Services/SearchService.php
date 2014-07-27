<?php

namespace Emhar\SearchDoctrineBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Emhar\SearchDoctrineBundle\Request\RequestType;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaDataFactory;
use Emhar\SearchDoctrineBundle\Query\QueryFactory;
use Emhar\SearchDoctrineBundle\Request\Request;

/**
 * SearchService.
 *
 * @author emhar
 */
class SearchService
{

	/**
	 * injected entity manager
	 * 
	 * @var EntityManager 
	 */
	protected $em;

	/**
	 * injected form factory
	 * @var FormFactory 
	 */
	protected $formFactory;

	/**
	 * @var ItemMetaDataFactory
	 */
	protected $itemMetaDataFactory;
	protected $queryFactory;

	/**
	 * Constructor
	 *
	 * @param EntityManager $em
	 * @param FormFactory $formFactory
	 * @param Cache $cache
	 */
	public function __construct(EntityManager $em, FormFactory $formFactory, Cache $cache = null)
	{
		$this->em = $em;
		$this->phpFileCache = $cache;
		$this->formFactory = $formFactory;
		$this->itemMetaDataFactory = new ItemMetaDataFactory($cache);
		$this->queryFactory = new QueryFactory($em, $cache);
	}

	/**
	 * Get Search form
	 *
	 * @param string $itemClass
	 * @return Form
	 */
	public function getForm($itemClass, $action)
	{
		$itemMetaData = $this->itemMetaDataFactory->getItemMetaData($itemClass);
		$form = $this->formFactory->create(
				new RequestType($itemMetaData), new Request(), array(
			'action' => $action,
			'method' => 'GET'
				)
		);
		return $form;
	}

	/**
	 * @param string $itemClass
	 * @param Form $form
	 * @param int $page
	 * @return array
	 */
	public function getResults($itemClass, Form $form, $page = 1)
	{
		$itemMetaData = $this->itemMetaDataFactory->getItemMetaData($itemClass);
		$query = $this->queryFactory->getQuery($itemMetaData);
		$request = $form->getData();
		$results = $query->getResults($this->em, $request, $page);
		return $results;
	}

	/**
	 * Return page number (begins by 1, not 0)
	 *
	 * @param string $itemClass
	 * @param Form $form
	 * @return int
	 */
	public function getPageCount($itemClass, Form $form)
	{
		$itemMetaData = $this->itemMetaDataFactory->getItemMetaData($itemClass);
		$query = $this->queryFactory->getQuery($itemMetaData);
		$request = $form->getData();
		$count = ceil($query->getCount($this->em, $request) / $request->getLimit());
		return $count;
	}
}
