<?php
class App_Model_Mongodb_RequestLog extends Shanty_Mongo_Document
{
	protected static $_db = 'hol';
	protected static $_collection = 'requestlog';
	
	public static function referral()
	{
		$date = [
			'$gte' => strtotime(date('Y-m-d 23:59:59', strtotime('-2 days'))),
			'$lte' => new Zend_Db_Expr('NOW()'),
		];
	
		$match = [
			'access_time' => $date,
			'kopel' => [
				'$exists' => true
			]
		];
	
		return self::getMongoCollection()->aggregate(
			['$match' => $match],
				[
				'$group' => [
					'_id' => '$kopel',
					'total' => ['$sum' => 1]
				]
			],
			[
				'$sort' => ['total' => -1]
			]
		);
	}
}