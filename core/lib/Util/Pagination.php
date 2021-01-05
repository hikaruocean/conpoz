<?php
namespace Conpoz\Core\Lib\Util;

class Pagination
{
    public $params = array();
    public function __construct ($params = array())
    {
        $this->params = array_merge(array(
            'pageKeyName' => 'page',
            'perPageNums' => 10,
            'displayPageNums' => 10,
        ), $params);
    }

    public function __set ($key, $val)
    {
        $this->params[$key] = $val;
        return true;
    }

    public function __get ($key)
    {
        if (!isset($this->params[$key])) {
            return null;
        }
        return $this->params[$key];
    }

    public function getPagePosition ($page)
    {
        if ($page < 1) {
            throw new \Exception('Page must greater equal than 1');
        }
        return array('limit' => $this->params['perPageNums'], 'offset' => ($page - 1) * $this->params['perPageNums']);
    }

    public function getPageInfo ($rowNums, $page, $uri)
    {
        $maxPage = ceil($rowNums / $this->params['perPageNums']);
        $page = $page > $maxPage ? $maxPage : $page;
        $middlePos = floor(($this->params['displayPageNums'] - 1) / 2);
        if ($page <= $middlePos) {
            $middlePos = $page - 1;
        } else if ($page + $middlePos >= $maxPage) {
            $middlePos = ($page + $middlePos - $maxPage) ;
        }
        $startPage = $page - $middlePos;
        $endPage = $startPage + $this->params['displayPageNums'] - 1;
        $prevPage = $page - 1 < 1 ? 1 : $page - 1;
        $nextPage = $page + 1 > $maxPage ? $maxPage : $page + 1;

        $urlPath = $uri;
        return array(
            'maxPage' => $maxPage,
            'startPage' => $startPage,
            'endPage' => $endPage > $maxPage ? $maxPage : $endPage,
            'urlPath' => $urlPath,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
        );
    }
}
