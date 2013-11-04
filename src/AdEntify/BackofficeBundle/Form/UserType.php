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
            ->add('usernameCanonical')
            ->add('email')
            ->add('emailCanonical')
            ->add('enabled')
            ->add('salt')
            ->add('password')
            ->add('lastLogin')
            ->add('locked')
            ->add('expired')
            ->add('expiresAt')
            ->add('confirmationToken')
            ->add('passwordRequestedAt')
            ->add('roles')
            ->add('credentialsExpired')
            ->add('credentialsExpireAt')
            ->add('firstname')
            ->add('lastname')
            ->add('createdAt')
            ->add('facebookId')
            ->add('facebookUsername')
            ->add('facebookAccessToken')
            ->add('twitterId')
            ->add('twitterUsername')
            ->add('twitterAccessToken')
            ->add('gender')
            ->add('photosCount')
            ->add('followingsCount')
            ->add('followersCount')
            ->add('lastFriendsListUpdate')
            ->add('locale')
            ->add('tagsCount')
            ->add('followedBrandsCount')
            ->add('person')
            ->add('followings')
            ->add('followers')
            ->add('friends')
            ->add('favoritesPhotos')
            ->add('clients')
            ->add('followedBrands')
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
