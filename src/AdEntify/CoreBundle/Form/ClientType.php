<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 13/10/2014
 * Time: 15:09
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	$builder->add('name', 'text')
	    ->add('displayName', 'text', array(
		'required' => false
	    ))
	    ->add('redirectUris', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
	$resolver->setDefaults(array(
	    'data_class' => 'AdEntify\CoreBundle\Entity\OAuth\Client',
	    'csrf_protection' => false
	));
    }

    public function getName()
    {
	return 'client';
    }
}