<?php

namespace Emhar\SearchDoctrineBundle\Tests\Functional;

use Emhar\SearchDoctrineBundle\Tests\BundleOrmTestCase;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item3;
use Emhar\SearchDoctrineBundle\Services\SearchService;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\Extension\Core\CoreExtension;

/**
 * FormTest.
 *
 * @author Emhar
 */
class FormTest extends BundleOrmTestCase
{

	public function testGetForm()
	{
		$cache = null;
		$resolvedTypeFactory = new ResolvedFormTypeFactory();
		$formRegistry = new FormRegistry(array(new CoreExtension()), $resolvedTypeFactory);
		$formFactory = new FormFactory($formRegistry, $resolvedTypeFactory);

		$searchService = new SearchService($this->getEntityManager(), $formFactory, $cache);
		$form = $searchService->getForm(Item3::getClass(), '');
		$view = $form->createView();

		$this->assertInstanceOf('Emhar\SearchDoctrineBundle\Request\Request', $view->vars['value']);
		$this->assertArrayHasKey('searchText', $view->children);
		$this->assertArrayHasKey('limit', $view->children);
	}
}
