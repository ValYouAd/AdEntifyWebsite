<?php

namespace AdEntify\BackofficeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('enabled', 'checkbox', array(
                'required' => false
            ))
            ->add('locked', 'checkbox', array(
                'required' => false
            ))
            ->add('expired', 'checkbox', array(
                'required' => false
            ))
            ->add('firstname', 'text', array(
                'required' => false
            ))
            ->add('lastname', 'text', array(
                'required' => false
            ))
            ->add('gender')
            ->add('photosCount')
            ->add('followingsCount')
            ->add('followersCount')
            ->add('locale')
            ->add('tagsCount')
            ->add('followedBrandsCount')
            ->add('points')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adentify_backofficebundle_user';
    }
}
