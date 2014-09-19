<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 03/09/2014
 * Time: 11:34
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('token', 'text')
            ->add('platform', 'text')
            ->add('operatingSystem', 'text')
            ->add('appVersion', 'text')
            ->add('locale', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Device',
            'intention' => 'device_item'
        ));
    }

    public function getName()
    {
        return 'device';
    }
}