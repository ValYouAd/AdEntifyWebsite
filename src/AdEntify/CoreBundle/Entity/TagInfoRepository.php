<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 23/02/15
 * Time: 16:05
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TagInfoRepository extends EntityRepository
{
    /**
     * Create tag info
     *
     * @param $tag
     * @param $type
     * @param $options
     * @return TagInfo
     */
    public function createTagInfo($tag, $type = TagInfo::TYPE_ADVERTISING, $options = array())
    {
        if (!$tag)
            throw new HttpException(400, 'You have to set a tag.');

        $tagInfo = new TagInfo();
        $tagInfo->setTag($tag)->setType($type)->setInfo($options);

        $this->getEntityManager()->persist($tagInfo);

        return $tagInfo;
    }

    /**
     * Create tag info for advertising tag
     *
     * @param $tag
     * @param $code
     * @param int $width
     * @param int $height
     * @param null $provider
     * @return TagInfo
     */
    public function createAdTagInfo($tag, $code, $width = 300, $height = 50, $provider = null)
    {
        if (!is_numeric($width) || !is_numeric($height))
            throw new HttpException(400, 'Width and/or height must be numeric.');
        if (empty($code))
            throw new HttpException(400, 'You have to enter a code.');

        $options = array(
            'dimensions' => array(
                'width' => $width,
                'height' => $height,
            ),
            'code' => $code,
            'provider' => $provider
        );

        return $this->createTagInfo($tag, TagInfo::TYPE_ADVERTISING, $options);
    }
}