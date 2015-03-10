<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 28/05/2013
 * Time: 18:40
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Form;

use Doctrine\ORM\EntityRepository;
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
            ->add('link', 'url', array(
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
                'description' => 'Photo ID. Get the right id with the GET operations of photos endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                        return $options['photoId'] > 0 ? $er->createQueryBuilder('p')
                            ->where('p.id = :id')
                            ->setParameter('id', $options['photoId'])
                            : $er->createQueryBuilder('p');
                }
            ))
            ->add('venue', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Venue',
                'property' => 'name',
                'required' => false,
                'description' => 'Venue ID. Get the right id with the GET operations of venues endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                        return $options['venueId'] > 0 ? $er->createQueryBuilder('v')
                            ->where('v.id = :id')
                            ->setParameter('id', $options['venueId'])
                            : $er->createQueryBuilder('p');
                    }
            ))
            ->add('product', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Product',
                'property' => 'name',
                'required' => false,
                'description' => 'Product ID. Get the right id with the GET operations of products endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                        return $options['productId'] > 0 ? $er->createQueryBuilder('p')
                            ->where('p.id = :id')
                            ->setParameter('id', $options['productId'])
                            : $er->createQueryBuilder('p');
                    }
            ))
            ->add('productType', 'entity', array(
                'class' => 'AdEntifyCoreBundle:ProductType',
                'property' => 'name',
                'required' => false,
                'description' => 'Product Type ID. Get the right id with the GET operations of products endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                        return $options['productTypeId'] > 0 ? $er->createQueryBuilder('p')
                            ->where('p.id = :id')
                            ->setParameter('id', $options['productTypeId'])
                            : $er->createQueryBuilder('p');
                    }
            ))
            ->add('person', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Person',
                'property' => 'name',
                'required' => false,
                'description' => 'Person ID. Get the right id with the GET operations of persons endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                        return $options['personId'] > 0 ? $er->createQueryBuilder('p')
                            ->where('p.id = :id')
                            ->setParameter('id', $options['personId'])
                            : $er->createQueryBuilder('p');
                    }
            ))
            ->add('brand', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Brand',
                'property' => 'name',
                'required' => false,
                'description' => 'Brand ID. Get the right id with the GET operations of brands endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                        return $options['brandId'] > 0 ? $er->createQueryBuilder('b')
                            ->where('b.id = :id')
                            ->setParameter('id', $options['brandId'])
                            : $er->createQueryBuilder('p');
                    }
            ))
            ->add('tagInfo', 'text', array(
                'required' => false
            ))
            ->add('save', 'submit', array('label' => 'Create'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Tag',
            'intention' => 'tag_item',
            'photoId' => 0,
            'venueId' => 0,
            'productId' => 0,
            'personId' => 0,
            'brandId' => 0,
            'productTypeId' => 0
        ));
    }

    public function getName()
    {
        return 'tag';
    }
}