<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 09/07/2013
 * Time: 09:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text')
            ->add('original_url', 'text')
            ->add('medium_url', 'text')
            ->add('small_url', 'text')
            ->add('description', 'textarea')
            ->add('purchase_url', 'text',  array(
                'required' => false
            ))
            ->add('purchaseVenues', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Venue',
                'property' => 'name',
                'required' => false,
                'description' => 'Array of venue ID. Get the right id with the GET operations of venues endpoint'
            ))
            ->add('brand', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Brand',
                'property' => 'name'
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Product',
            'intention' => 'product_item'
        ));
    }

    public function getName()
    {
        return 'product';
    }
}