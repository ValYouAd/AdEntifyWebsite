<?php
namespace AdEntify\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_Extension;


class AdEntifyExtension extends Twig_Extension
{
    protected $container;
    private   $em;
    private   $connection;
    private   $locale;

    public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container) {
        $this->container = $container;
//        $this->container->get('request')->setlocale('fr');

        $this->em = $em;
        $this->connection = $em->getConnection();
        $this->locale = $this->container->get('request')->getlocale();
    }

    public function getFunctions()
    {
        return array(
            'terms_of_use' => new \Twig_Function_Method($this, 'getTermsOfUse'),
            'privacy' => new \Twig_Function_Method($this, 'getPrivacy'),
            'legal_notices' => new \Twig_Function_Method($this, 'getLegalNotices'),
        );
    }

    public function getTermsOfUse()
    {
//        echo $this->locale;die;

        $sql = "SELECT terms_of_use FROM legal WHERE language='$this->locale'";
        return $this->connection->fetchColumn($sql);
    }

    public function getName()
    {
        return 'AdEntify_extension';
    }
}