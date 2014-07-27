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
 * AttributeTypeTest.
 *
 * @author Emhar
 */
class AttributeTypeTest extends BundleDataOrmTestCase
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

	public function testResultTypes()
	{
		$form = $this->searchService->getForm($this->itemClass, '');
		/* @var $request \Emhar\SearchDoctrineBundle\Request\Request */
		$request = $form->getData();
		$request->setSearchText('joe bob');
		$request->setLimit(2);
		$form->setData($request);
		$results = $this->searchService->getResults($this->itemClass, $form);
		foreach($results as $result)
		{
			foreach((array) $result as $attributeName => $attribute)
			{
				if(strpos($attributeName, 'Converted') !== false
						|| in_array($attributeName, array('type', 'string')))
				{
					$this->assertInternalType('string', $attribute);
				}
				elseif(in_array($attributeName, array('id', 'score', 'join')))
				{
					$this->assertInternalType('int', $attribute);
				}
				else
				{
					switch($result->type)
					{
						case 'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity1':
							$this->assertNull($attribute);
							break;
						case 'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2':
							switch($attributeName)
							{
								case 'unbinded':
									$this->assertNull($attribute);
									break;
								case 'integer':
								case 'smallint':
									$this->assertInternalType('int', $attribute);
									break;
								case 'bigint':
									$this->assertInternalType('string', $attribute);
									$this->assertTrue(filter_var($attribute, FILTER_VALIDATE_INT) !== false);
									break;
								case 'boolean':
									$this->assertInternalType('bool', $attribute);
									break;
								case 'float':
									$this->assertInternalType('float', $attribute);
									break;
								case 'date':
								case 'time':
								case 'datetime':
								case 'datetimetz':
									$this->assertInstanceOf('DateTime', $attribute);
									break;
								case 'decimal':
									$this->assertInternalType('string', $attribute);
									$this->assertTrue(filter_var($attribute, FILTER_VALIDATE_FLOAT) !== false);
									break;
								case 'text':
									$this->assertInternalType('string', $attribute);
									break;
							}
							break;
					}
				}
			}
		}
	}
}
