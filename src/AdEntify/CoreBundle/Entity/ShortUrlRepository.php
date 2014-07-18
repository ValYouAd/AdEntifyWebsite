<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 17/07/2013
 * Time: 12:44
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use AdEntify\CoreBundle\Util\CommonTools;
use Doctrine\ORM\EntityRepository;

class ShortUrlRepository extends EntityRepository
{
    /**
     * @param $id
     * @return ShortUrl|bool
     */
    public function findByBase62IdAndUpdateCounter($id)
    {
        $id = CommonTools::base_to($id, 62, 10);
        if ($id != false) {
            $shortUrl = $this->getEntityManager()->getRepository('AdEntifyCoreBundle:ShortUrl')->find($id);
            if ($shortUrl) {
                $shortUrl->setVisitCount($shortUrl->getVisitCount() + 1);
                $this->getEntityManager()->merge($shortUrl);
                $this->getEntityManager()->flush();

                return $shortUrl;
            } else
                return false;
        } else
            return false;
    }

    /**
     * @param $url
     */
    public function createShortUrl($url)
    {
        $shortUrl = new ShortUrl();
        $shortUrl->setUrl($url);
        $this->getEntityManager()->persist($shortUrl);
        $this->getEntityManager()->flush();
    }
}