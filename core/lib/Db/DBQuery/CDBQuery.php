<?php 
namespace Conpoz\Lib\Db\DBQuery;

class CDBQuery extends \Conpoz\Lib\Db\DBQuery
{
    protected function beforeInsert() 
    {
        $datetime = date('Y-m-d H:i:s');
        $this->data['created'] = $datetime;
        $this->data['updated'] = $datetime;
    }

    protected function beforeUpdate() 
    {
        $datetime = date('Y-m-d H:i:s');
        $this->data['updated'] = $datetime;
    }
}