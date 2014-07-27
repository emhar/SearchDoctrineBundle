<?php

namespace Emhar\SearchDoctrineBundle\Tests\Functional;

use Emhar\SearchDoctrineBundle\Tests\BundleDataOrmTestCase;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item3;
use Emhar\SearchDoctrineBundle\Services\SearchService;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\Extension\Core\CoreExtension;

/**
 * ResultCountTest.
 *
 * @author Emhar
 */
class ResultCountTest extends BundleDataOrmTestCase
{

	/**
	 * @var SearchService
	 */
	protected $searchService;

	/**
	 * @var string
	 */
	protected $itemClass;

	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		$this->itemClass = Item3::getClass();

		$cache = null;
		$resolvedTypeFactory = new ResolvedFormTypeFactory();
		$formRegistry = new FormRegistry(array(new CoreExtension()), $resolvedTypeFactory);
		$formFactory = new FormFactory($formRegistry, $resolvedTypeFactory);

		$this->searchService = new SearchService($this->getEntityManager(), $formFactory, $cache);
		parent::__construct($name, $data, $dataName);
	}

	/**
	 * Test the limit parameter
	 *
	 * @dataProvider limitProvider
	 */
	public function testLimit($searchTest, $limit, $page, $resultNumber)
	{
		$form = $this->searchService->getForm($this->itemClass, '');

		/* @var $request \Emhar\SearchDoctrineBundle\Request\Request */
		$request = $form->getData();
		$request->setSearchText($searchTest);
		$request->setLimit($limit);
		$form->setData($request);
		$results = $this->searchService->getResults($this->itemClass, $form, $page);

		$this->assertCount($resultNumber, $results);
	}

	public function limitProvider()
	{
		return array(
			array('bob joe', 2, 1, 2),
			array('bob joe', 2, 2, 1),
			array('bob joe', 2, 3, 0),
			array('azerty', 2, 1, 0)
		);
	}

	/**
	 * Test the limit parameter
	 *
	 * @dataProvider countProvider
	 */
	public function testCount($searchTest, $count)
	{
		$form = $this->searchService->getForm($this->itemClass, '');

		/* @var $request \Emhar\SearchDoctrineBundle\Request\Request */
		$request = $form->getData();
		$request->setSearchText($searchTest);
		$request->setLimit(1);
		$form->setData($request);
		$actualCount = $this->searchService->getPageCount($this->itemClass, $form);
		$this->assertEquals($count, $actualCount);
	}

	public function countProvider()
	{
		return array(
			array('bob joe', 3),
			array('joe bob', 3),
			array('nick', 2),
			array('nicky', 1),
			array('azerty', 0)
		);
	}
}
