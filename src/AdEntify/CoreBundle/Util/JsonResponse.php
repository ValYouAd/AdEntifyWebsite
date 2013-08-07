<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 31/07/2013
 * Time: 11:17
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Util;

use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    /**
     * Sets the JSON data to be sent
     *
     * @param $data
     * @param bool $encode
     * @return mixed
     */
    public function setJsonData($data)
    {
        $this->data = $data;

        return $this->update();
    }
}