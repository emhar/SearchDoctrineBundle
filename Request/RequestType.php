<?php

namespace Emhar\SearchDoctrineBundle\Request;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaData;

/**
 * RequestType.
 *
 * @author Emhar
 */
class RequestType extends AbstractType
{

	/**
	 * @var ItemMetaData
	 */
	protected $itemMetaData;

	/**
	 * @param ItemMetaData $itemMetaData
	 */
	public function __construct(ItemMetaData $itemMetaData)
	{
		$this->itemMetaData = $itemMetaData;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('searchText', 'search')
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
