<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 07/11/14
 * Time: 15:01
 */

namespace AdEntify\CoreBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnalyticType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('action', 'text')
            ->add('element', 'text')
            ->add('platform', 'text')
            ->add('link', 'text', array(
                'required' => false
            ))
            ->add('tag', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Tag',
                'multiple' => false,
                'property' => 'title',
                'required' => false,
                'description' => 'Tag ID. Get the right id with the GET operations of tags endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                    return $options['tagId'] > 0 ? $er->createQueryBuilder('t')
                        ->where('t.id = :id')
                        ->setParameter('id', $options['tagId'])
                        : $er->createQueryBuilder('t');
                }
            ))
            ->add('user', 'entity', array(
                'class' => 'AdEntifyCoreBundle:User',
                'multiple' => false,
                'property' => 'username',
                'required' => false,
                'description' => 'User ID. Get the right id with the GET operations of users endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                    return $options['userId'] > 0 ? $er->createQueryBuilder('u')
                        ->where('u.id = :id')
                        ->setParameter('id', $options['userId'])
                        : $er->createQueryBuilder('u');
                }
            ))
            ->add('photo', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Photo',
                'multiple' => false,
                'property' => 'name',
                'required' => false,
                'description' => 'Photo ID. Get the right id with the GET operations of photos endpoint',
                'query_builder' => function(EntityRepository $er) use($options) {
                    return $options['photoId'] > 0 ? $er->createQueryBuilder('p')
                        ->where('p.id = :id')
                        ->setParameter('id', $options['photoId'])
                        : $er->createQueryBuilder('p');
                }
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Analytic',
            'csrf_protection' => false,
            'photoId' => 0,
            'tagId' => 0,
            'userId' => 0,
        ));
    }

    public function getName()
    {
        return 'analytic';
    }
}