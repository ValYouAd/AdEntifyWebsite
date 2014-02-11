<?php

namespace AdEntify\BackofficeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategoryType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', 'a2lix_translations_gedmo', array(
                'translatable_class' => 'AdEntify\CoreBundle\Entity\Category',
                'required' => true,
                /*'fields' => array(
                    'name' => array(
                        'type' => 'text'
                    ),
                )*/
            ))
            ->add('displayOrder')
            ->add('visible', 'checkbox');
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Category'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adentify_backofficebundle_category';
    }
}
