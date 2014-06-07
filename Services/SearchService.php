<?php

namespace Emhar\SearchDoctrineBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Emhar\SearchDoctrineBundle\Request\RequestType;
use Emhar\SearchDoctrineBundle\Mapping\ViewFactory;
use Emhar\SearchDoctrineBundle\Query\Query;
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
	 * @var ViewFactory
	 */
	protected $viewFactory;

	/**
	 * Constructor
	 *
	 * @param EntityManager $em
	 * @param Cache $cache
	 * @param FormFactory $formFactory
	 */
	public function __construct(EntityManager $em, Cache $cache, FormFactory $formFactory)
	{
		$this->em = $em;
		$this->phpFileCache = $cache;
		$this->formFactory = $formFactory;
		$this->viewFactory = new ViewFactory($cache, $em);
	}

	/**
	 * Get Search form
	 *
	 * @param string $viewName
	 * @return Form
	 */
	public function getForm($viewName, $action)
	{
		$view = $this->viewFactory->getView($viewName);
		$form = $this->formFactory->create(new RequestType($view), new Request(), array(
			'action' => $action));
		return $form;
	}

	/**
	 * @param string $viewName
	 * @param Form $form
	 * @param int $page
	 * @return array
	 */
	public function getResults($viewName, Form $form, $page = 1)
	{
		$view = $this->viewFactory->getView($viewName);
		$query = new Query($view, $this->em);
		$request = $form->getData();
		$results = $query->getResults($request, $page);
		return $results;
	}

	/**
	 * @param string $viewName
	 * @param Form $form
	 * @return int
	 */
	public function getPageCount($viewName, Form $form)
	{
		$view = $this->viewFactory->getView($viewName);
		$query = new Query($view, $this->em);
		$request = $form->getData();
		$count = ceil($query->getCount($request) / $request->getLimit());
		return $count;
	}

}
