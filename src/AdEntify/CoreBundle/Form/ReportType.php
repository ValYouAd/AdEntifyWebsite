<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 14/11/2013
 * Time: 18:39
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('reason', 'textarea')
            ->add('tag', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Tag',
                'property' => 'title',
                'required' => false
            ))
            ->add('photo', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Photo',
                'property' => 'caption',
                'required' => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Report',
            'intention' => 'report_item'
        ));
    }

    public function getName()
    {
        return 'report';
    }
}