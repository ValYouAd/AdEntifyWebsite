<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 28/05/2013
 * Time: 18:40
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VenueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('foursquareId', 'hidden')
            ->add('foursquareShortLink', 'text')
            ->add('name', 'text')
            ->add('description', 'textarea', array(
                'required' => false
            ))
            ->add('link', 'text')
            ->add('lat', 'text')
            ->add('lng', 'text')
            ->add('address', 'text', array(
                'required' => false
            ))
            ->add('cc', 'text',array(
                'required' => false
            ))
            ->add('city', 'text',array(
                'required' => false
            ))
            ->add('country', 'text',array(
                'required' => false
            ))
            ->add('postalCode', 'text',array(
                'required' => false
            ))
            ->add('state', 'text',array(
                'required' => false
            ))
            ->add('products', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Product',
                'property' => 'name',
                'required' => false,
                'multiple' => true
            ))
            ->add('brands', 'entity', array(
                'class' => 'AdEntifyCoreBundle:Brand',
                'property' => 'name',
                'required' => false,
                'multiple' => true
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdEntify\CoreBundle\Entity\Venue',
            'intention' => 'venue_item'
        ));
    }

    public function getName()
    {
        return 'venue';
    }
}