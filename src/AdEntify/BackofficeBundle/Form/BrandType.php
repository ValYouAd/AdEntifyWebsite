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
            ->add('name', 'text', array(
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('description', 'textarea', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('websiteUrl', 'url', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('facebookUrl', 'url', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('twitterUrl', 'url', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('pinterestUrl', 'url', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('instagramUrl', 'url', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('originalLogoUrl', 'file', array(
                'label' => 'Logo',
                'data_class' => null,
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('logoUrl', 'url', array(
                'required' => false,
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('productsCount', 'integer', array(
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('tagsCount', 'integer', array(
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('venuesCount', 'integer', array(
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            ->add('costPerTag', 'money', array(
                'label_attr' => array(
                    'class' => 'col-md-2 control-label'
                ),
                'attr' => array(
                    'class' => 'form-control'
                )
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
