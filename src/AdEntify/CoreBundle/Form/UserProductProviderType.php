<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 26/11/2014
 * Time: 11:07
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserProductProviderType extends AbstractType{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('apiKey', 'text', array(
                'required' => false
            ))
            ->add('productProviders', 'entity', array(
                'class' => 'AdEntifyCoreBundle:ProductProvider',
                'property' => 'id',
                'required' => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\UserProductProvider',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'userProductProvider';
    }
}