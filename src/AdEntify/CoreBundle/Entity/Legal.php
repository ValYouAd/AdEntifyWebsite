<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Legal
 *
 * @ORM\Table(name="legal")
 * @ORM\Entity
 *
 */
class Legal
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="terms_of_use", type="string", length=4096)
     */
    private $termsOfUse;

    /**
     * @var string
     *
     * @ORM\Column(name="privacy", type="string", length=4096)
     */
    private $privacy;

    /**
     * @var string
     *
     * @ORM\Column(name="legal_notices", type="string", length=4096)
     */
    private $legalNotices;


    /**
     * @var language
     *
     * @ORM\Column(name="language", type="string", length=255, unique=true)
     */
    private $language;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set termsOfUse
     *
     * @param string $termsOfUse
     * @return Legal
     */
    public function setTermsOfUse($termsOfUse)
    {
        $this->termsOfUse = $termsOfUse;
    
        return $this;
    }

    /**
     * Get termsOfUse
     *
     * @return string 
     */
    public function getTermsOfUse()
    {
        return $this->termsOfUse;
    }

    /**
     * Set privacy
     *
     * @param string $privacy
     * @return Legal
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    
        return $this;
    }

    /**
     * Get privacy
     *
     * @return string 
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * Set legalNotices
     *
     * @param string $legalNotices
     * @return Legal
     */
    public function setLegalNotices($legalNotices)
    {
        $this->legalNotices = $legalNotices;
    
        return $this;
    }

    /**
     * Get legalNotices
     *
     * @return string 
     */
    public function getLegalNotices()
    {
        return $this->legalNotices;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Legal
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    
        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }
}