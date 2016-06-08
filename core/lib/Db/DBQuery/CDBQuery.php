<?php 
namespace Conpoz\Lib\Db\DBQuery;

class CDBQuery extends \Conpoz\Lib\Db\DBQuery
{
	protected function beforeInsert(&$table, &$data) {
		$datetime = date('Y-m-d H:i:s');
		$data['created'] = $datetime;
		$data['updated'] = $datetime;
	}

	protected function beforeUpdate(&$table, &$data) {
		$datetime = date('Y-m-d H:i:s');
		$data['updated'] = $datetime;
	}
}