<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 03/06/2013
 * Time: 09:54
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('firstname', 'text', array(
                'required' => false
            ))
            ->add('lastname', 'text', array(
                'required' => false
            ))
            ->add('facebookId', 'text', array(
                'required' => false
            ))
            ->add('gender', 'text', array(
                'required' => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Person',
            'intention' => 'person_item'
        ));
    }

    public function getName()
    {
        return 'person';
    }
}