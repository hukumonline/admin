<?php
class JobController extends Application_Controller_Cli
{
	const NAME_ORDERQUEUE = 'job_queue';
	public function runJobAction()
	{
		$resource = Pandamp_Application::getOption('resources')['multidb'];
		$options = array(
				'name'          => self::NAME_ORDERQUEUE,
				'driverOptions' => array(
					'host'      => $resource['db1']['host'],
					'port'      => '3306',
					'username'  => $resource['db1']['username'],
					'password'  => $resource['db1']['password'],
					'dbname'    => $resource['db1']['dbname'],
					'type'      => $resource['db1']['adapter']
				)
		);
		
		include_once( 'Pandamp/Job/Queue.php' );
		$queue = new Pandamp_Job_Queue('Db', $options);
		$queue->runJobs();
	}
}