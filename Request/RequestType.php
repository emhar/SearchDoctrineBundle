<?php

namespace Emhar\SearchDoctrineBundle\Request;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Emhar\SearchDoctrineBundle\Mapping\View;

/**
 * RequestType.
 *
 * @author Emhar
 */
class RequestType extends AbstractType
{

	/**
	 * @var View
	 */
	protected $view;

	/**
	 * @param View $view
	 */
	public function __construct(View $view)
	{
		$this->view = $view;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->setMethod("GET")
				->add('searchText', 'search')
				->add('limit', 'integer', array(
					'data' => 20
				))
				->add('submit', 'submit')
		;
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Emhar\SearchDoctrineBundle\Request\Request',
			'csrf_protection' => false,
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'q';
	}

}
