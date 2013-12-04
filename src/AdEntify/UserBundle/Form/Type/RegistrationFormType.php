<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 15/10/2013
 * Time: 16:35
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('firstname', 'text', array(
                'label' => null,
                'attr' => array(
                    'placeholder' => 'Firstname',
                    'class' => 'form-control'
                )
            ))
            ->add('username', 'hidden')
            ->add('lastname', 'text', array(
                'label' => null,
                'attr' => array(
                    'placeholder' => 'Lastname',
                    'class' => 'form-control'
                )
            ))
            ->add('email', 'email', array(
                'label' => null,
                'attr' => array(
                    'placeholder' => 'Email',
                    'class' => 'form-control'
                )
            ))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => false, 'attr' => array(
                    'placeholder' => 'Password',
                    'class' => 'form-control'
                )),
                'second_options' => array('label' => false, 'attr' => array(
                    'placeholder' => 'Confirm password',
                    'class' => 'form-control'
                )),
                'invalid_message' => 'fos_user.password.mismatch',
            ));
    }

    public function getName()
    {
        return 'adentify_user_registration';
    }
}