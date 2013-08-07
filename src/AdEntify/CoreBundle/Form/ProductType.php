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
            ->add('description', 'text')
            ->add('facebookId', 'text')
            ->add('purchase_url', 'text',array(
                'required' => false
            ))
            ->add('tag', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Tag',
                'property' => 'title',
                'required' => false
            ))
            ->add('owner', 'entity', array(
                'class' => 'AdEntifyCoreBundle:User',
                'property' => 'username',
                'required' => false
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