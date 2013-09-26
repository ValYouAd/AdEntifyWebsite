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
                'required' => false,
                'description' => 'Link to the object'
            ))
            ->add('x_position', 'text', array(
                'description' => 'Position of the tag on the photo. Should be a number between 0 to 1.0, inclusive. 0 is the left edge of the photo, 1.0 is the right.'
            ))
            ->add('y_position', 'text', array(
                'description' => 'Position of the tag on the photo. Should be a number between 0 to 1.0, inclusive. 0 is the top edge of the photo, 1.0 is the bottom.'
            ))
            ->add('photo', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Photo',
                'property' => 'caption',
                'description' => 'Photo ID. Get the right id with the GET operations of photos endpoint'
            ))
            ->add('venue', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Venue',
                'property' => 'name',
                'required' => false,
                'description' => 'Venue ID. Get the right id with the GET operations of venues endpoint'
            ))
            ->add('product', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Product',
                'property' => 'name',
                'required' => false,
                'description' => 'Product ID. Get the right id with the GET operations of products endpoint'
            ))
            ->add('person', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Person',
                'property' => 'fullname',
                'required' => false,
                'description' => 'Person ID. Get the right id with the GET operations of persons endpoint'
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