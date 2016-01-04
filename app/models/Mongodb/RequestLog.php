<?php
class App_Model_Mongodb_RequestLog extends Shanty_Mongo_Document
{
	protected static $_db = 'hol';
	protected static $_collection = 'requestlog';
	
	public static function device($device)
	{
		if ($device == 'desktop')
			$device = "www.hukumonline.com";
		else
			$device = "m.hukumonline.com";
		
		$query = [
		'access_time' => [
		'$gte' => new \MongoDate( strtotime('-1 minute') ),
		'$lte' => new \MongoDate(),
		],
		'full_url' => new \MongoRegex("/".$device."/i"),
		];
		$total = self::all()->count();
		$pipeline = [
			[
				'$match' => [
					'access_time' => [
						'$gte' => new \MongoDate( strtotime('-1 minute') ),
						'$lte' => new \MongoDate(),
					],
					'full_url' => new \MongoRegex("/".$device."/i"),
				]
			],
			[
				'$group' => [
					'_id' => 0,
					'count' => ['$sum' => 1]
				]
			],
			[
				'$project' => [
					'percentage' => [
						'$multiply' => [[
							'$divide' => [
								'$count', $total
							]],
							100
						]
					]
				]
			],
			[
				'$sort' => ['percentage' => -1]
			]
		];
		$options = ['allowDiskUse' => true];
		return self::getMongoCollection()->aggregate($pipeline);
	}
	public static function referral($periode)
	{
		$date = [
			'$gte' => new \MongoDate( strtotime(date('Y-m-d 23:59:59', strtotime('-2 days'))) ),
			'$lte' => new \MongoDate(),
		];
		
		if($periode == 'yesterday')
			$date = [
				'$gte' => new \MongoDate( strtotime(date('Y-m-d 23:59:59', strtotime('-4 days'))) ),
				'$lte' => new \MongoDate( strtotime(date('Y-m-d 00:00:00', strtotime('-2 days'))) ),
			];
	
		$match = [
			'access_time' => $date
		];
	
		return self::getMongoCollection()->aggregate(
			['$match' => $match],
				[
				'$group' => [
					'_id' => '$refer_url',
					'total' => ['$sum' => 1]
				]
			],
			[
				'$limit' => 10
			],
			[
				'$sort' => ['total' => -1]
			]
		);
	}
	
	public static function click($periode)
	{
		$date = [
			'$gte' => new \MongoDate( strtotime(date('Y-m-d 23:59:59', strtotime('-2 days'))) ),
			'$lte' => new \MongoDate(),
		];
		
		if($periode == 'yesterday')
			$date = [
				'$gte' => new \MongoDate( strtotime(date('Y-m-d 23:59:59', strtotime('-4 days'))) ),
				'$lte' => new \MongoDate( strtotime(date('Y-m-d 00:00:00', strtotime('-2 days'))) ),
			];
	
		$match = [
			'access_time' => $date
		];
	
		return self::getMongoCollection()->aggregate(
			['$match' => $match],
				[
				'$group' => [
					'_id' => '$full_url',
					'total' => ['$sum' => 1]
				]
			],
			[
				'$limit' => 10
			],
			[
				'$sort' => ['total' => -1]
			]
		);
	}
}