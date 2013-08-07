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
        $builder->add('caption', 'text');
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
