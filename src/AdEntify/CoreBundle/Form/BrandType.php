<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 10/01/2014
 * Time: 12:08
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text')
            ->add('website_url', 'url', array('required' => false))
            ->add('facebook_url', 'url', array('required' => false))
            ->add('twitter_url', 'url', array('required' => false))
            ->add('pinterest_url', 'url', array('required' => false))
            ->add('instagram_url', 'url', array('required' => false))
            ->add('tumblr_url', 'url', array('required' => false))
            ->add('original_logo_url', 'url', array('required' => false))
            ->add('large_logo_url', 'url', array('required' => false))
            ->add('medium_logo_url', 'url', array('required' => false))
            ->add('small_logo_url', 'url', array('required' => false))
            ->add('description', 'textarea', array('required' => false))
            ->add('categories', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Category',
                'property' => 'name',
                'required' => false,
                'multiple' => true,
                'description' => 'Category ID. Get the right id with the GET operations of categories endpoint'
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Brand',
            'intention' => 'brand_item'
        ));
    }

    public function getName()
    {
        return 'brand';
    }
} 