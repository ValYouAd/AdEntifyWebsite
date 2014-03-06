<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/05/2013
 * Time: 18:55
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('caption', 'text', array(
                'required' => false,
                'description' => 'Title or description of the photo'
            ))
            ->add('source', 'text', array(
                'required' => false,
                'description' => 'Source of the photo : facebook|flickr|instagram|googleplus|local|wordpress'
            ))
            ->add('photo_source_id', 'text', array(
                'required' => false,
                'description' => 'Photo ID of the source'
            ))
            ->add('original_url', 'text')
            ->add('original_width', 'text', array(
                    'required' => false
                ))
            ->add('original_height', 'text', array(
                    'required' => false
                ))
            ->add('large_url', 'text', array(
                'required' => false
            ))
            ->add('large_width', 'text', array(
                'required' => false
            ))
            ->add('large_height', 'text', array(
                'required' => false
            ))
            ->add('medium_url', 'text', array(
                'required' => false
            ))
            ->add('medium_width', 'text', array(
                'required' => false
            ))
            ->add('medium_height', 'text', array(
                'required' => false
            ))
            ->add('small_url', 'text', array(
                'required' => false
            ))
            ->add('small_width', 'text', array(
                'required' => false
            ))
            ->add('small_height', 'text', array(
                'required' => false
            ))
            ->add('latitude', 'number', array(
                'precision' => 6,
                'required' => false
            ))
            ->add('longitude', 'number', array(
                'precision' => 6,
                'required' => false
            ))
            ->add('categories', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Category',
                'property' => 'name',
                'required' => false,
                'multiple' => true,
                'description' => 'Category ID. Get the right id with the GET operations of categories endpoint'
            ))
            ->add('hashtags', 'text', array(
                'required' => false
            ));;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Photo',
            'intention' => 'photo_item'
        ));
    }

    public function getName()
    {
        return 'photo';
    }
}
