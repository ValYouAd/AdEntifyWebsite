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
            ->add('brand', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Brand',
                'property' => 'name',
                'required' => false
            ))
            ->add('roles', 'choice', array(
                'required' => true,
                'multiple' => true,
                'choices' => $this->refactorRoles($options['roles'])
            ));
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\User',
            'roles' => null
        ));

        //$resolver->resolve(array('roles'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adentify_backofficebundle_user';
    }

    private function refactorRoles($originRoles)
    {
        $roles = array();
        $rolesAdded = array();

        // Add herited roles
        foreach ($originRoles as $roleParent => $rolesHerit) {
            $tmpRoles = array_values($rolesHerit);
            $rolesAdded = array_merge($rolesAdded, $tmpRoles);
            $roles[$roleParent] = array_combine($tmpRoles, $tmpRoles);
        }
        // Add missing superparent roles
        $rolesParent = array_keys($originRoles);
        foreach ($rolesParent as $roleParent) {
            if (!in_array($roleParent, $rolesAdded)) {
                $roles['-----'][$roleParent] = $roleParent;
            }
        }

        return $roles;
    }
}
