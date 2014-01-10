<?php

namespace AdEntify\BackofficeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BrandType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('description', 'textarea', array(
                'required' => false,
            ))
            ->add('websiteUrl', 'url', array(
                'required' => false,
            ))
            ->add('facebookUrl', 'url', array(
                'required' => false,
            ))
            ->add('twitterUrl', 'url', array(
                'required' => false,
            ))
            ->add('pinterestUrl', 'url', array(
                'required' => false,
            ))
            ->add('instagramUrl', 'url', array(
                'required' => false,
            ))
            ->add('tumblrUrl', 'url', array(
                'required' => false,
            ))
            ->add('originalLogoUrl', 'file', array(
                'label' => 'Logo',
                'data_class' => null,
                'required' => false,
            ))
            ->add('logoUrl', 'url', array(
                'required' => false,
            ))
            ->add('productsCount', 'integer')
            ->add('tagsCount', 'integer')
            ->add('venuesCount', 'integer')
            ->add('costPerTag', 'money')
            ->add('categories', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Category',
                'property' => 'name',
                'required' => false,
                'multiple' => true,

            ))
            ->add('minAge', 'integer', array(
                'required' => false,
            ))
            ->add('legalNotice', 'text', array(
                'required' => false,
            ))
            ->add('validated', 'checkbox', array(
                'required' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Brand'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adentify_backofficebundle_brand';
    }
}
