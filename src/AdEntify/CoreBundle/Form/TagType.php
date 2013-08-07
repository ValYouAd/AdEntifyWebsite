<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 28/05/2013
 * Time: 18:40
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', 'text')
            ->add('title', 'text')
            ->add('description', 'textarea', array(
                'required' => false
            ))
            ->add('link', 'text', array(
                'required' => false
            ))
            ->add('x_position', 'text')
            ->add('y_position', 'text')
            ->add('photo', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Photo',
                'property' => 'caption',
            ))
            ->add('venue', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Venue',
                'property' => 'name',
                'required' => false
            ))
            ->add('product', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Product',
                'property' => 'name',
                'required' => false
            ))
            ->add('person', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Person',
                'property' => 'fullname',
                'required' => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Tag',
            'intention' => 'tag_item',
        ));
    }

    public function getName()
    {
        return 'tag';
    }
}