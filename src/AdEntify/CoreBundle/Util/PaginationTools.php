<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/07/2013
 * Time: 18:58
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Util;

class PaginationTools
{
    public static function getNextPrevPagination($count, $page, $limit, $controller, $url, $params = array()) {
        $pagination = null;
        if ($count > 0 && $page >= 1) {
            $pagination = array(
                'total' => $count
            );
            if ($page > 1) {
                $pagination['previous'] = $controller->generateUrl($url, array_merge($params, array(
                    'page' => $page - 1,
                    'limit' => $limit
                )), true);
            }
            if (ceil($count / $limit) > $page) {
                $pagination['next'] = $controller->generateUrl($url, array_merge($params, array(
                    'page' => $page + 1,
                    'limit' => $limit
                )), true);
            }
        }

        return $pagination;
    }

    public static function getPaginationArray($data, $pagination)
    {
        return array(
            'data' => $data ? $data : array(),
            'paging' => $pagination
        );
    }
}