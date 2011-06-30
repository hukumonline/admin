<?php

/**
 * Description of customController
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dev_CustomController extends Zend_Controller_Action
{
function datediff($tgl1, $tgl2){
 $tgl1 = (is_string($tgl1) ? strtotime($tgl1) : $tgl1);
 $tgl2 = (is_string($tgl2) ? strtotime($tgl2) : $tgl2);
 $diff_secs = abs($tgl1 - $tgl2);
 $base_year = min(date("Y", $tgl1), date("Y", $tgl2));
 $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
 
 $year = date("Y", $diff) - $base_year;
 if ($year !== 0) $year;
 
 
 return array( "years" => date("Y", $diff) - $base_year,
"months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
"months" => date("n", $diff) - 1,
"days_total" => floor($diff_secs / (3600 * 24)),
"days" => date("j", $diff) - 1,
"hours_total" => floor($diff_secs / 3600),
"hours" => date("G", $diff),
"minutes_total" => floor($diff_secs / 60),
"minutes" => (int) date("i", $diff),
"seconds_total" => $diff_secs,
"seconds" => (int) date("s", $diff)  );
 }
 
    function ago($datefrom,$dateto=-1)
    {
        // Defaults and assume if 0 is passed in that
        // its an error rather than the epoch
   
        if($datefrom==0) { return "A long time ago"; }
        if($dateto==-1) { $dateto = time(); }
       
        // Make the entered date into Unix timestamp from MySQL datetime field

        $datefrom = strtotime($datefrom);
   
        // Calculate the difference in seconds betweeen
        // the two timestamps

        $difference = $dateto - $datefrom;

        // Based on the interval, determine the
        // number of units between the two dates
        // From this point on, you would be hard
        // pushed telling the difference between
        // this function and DateDiff. If the $datediff
        // returned is 1, be sure to return the singular
        // of the unit, e.g. 'day' rather 'days'
   
        switch(true)
        {
            // If difference is less than 60 seconds,
            // seconds is a good interval of choice
            case(strtotime('-1 min', $dateto) < $datefrom):
                $datediff = $difference;
                $res = ($datediff==1) ? $datediff.' second ago' : $datediff.' seconds ago';
                break;
            // If difference is between 60 seconds and
            // 60 minutes, minutes is a good interval
            case(strtotime('-1 hour', $dateto) < $datefrom):
                $datediff = floor($difference / 60);
                $res = ($datediff==1) ? $datediff.' minute ago' : $datediff.' minutes ago';
                break;
            // If difference is between 1 hour and 24 hours
            // hours is a good interval
            case(strtotime('-1 day', $dateto) < $datefrom):
                $datediff = floor($difference / 60 / 60);
                $res = ($datediff==1) ? $datediff.' hour ago' : $datediff.' hours ago';
                break;
            // If difference is between 1 day and 7 days
            // days is a good interval               
            case(strtotime('-1 week', $dateto) < $datefrom):
                $day_difference = 1;
                while (strtotime('-'.$day_difference.' day', $dateto) >= $datefrom)
                {
                    $day_difference++;
                }
               
                $datediff = $day_difference;
                $res = ($datediff==1) ? 'yesterday' : $datediff.' days ago';
                break;
            // If difference is between 1 week and 30 days
            // weeks is a good interval           
            case(strtotime('-1 month', $dateto) < $datefrom):
                $week_difference = 1;
                while (strtotime('-'.$week_difference.' week', $dateto) >= $datefrom)
                {
                    $week_difference++;
                }
               
                $datediff = $week_difference;
                $res = ($datediff==1) ? 'last week' : $datediff.' weeks ago';
                break;           
            // If difference is between 30 days and 365 days
            // months is a good interval, again, the same thing
            // applies, if the 29th February happens to exist
            // between your 2 dates, the function will return
            // the 'incorrect' value for a day
            case(strtotime('-1 year', $dateto) < $datefrom):
                $months_difference = 1;
                while (strtotime('-'.$months_difference.' month', $dateto) >= $datefrom)
                {
                    $months_difference++;
                }
               
                $datediff = $months_difference;
                $res = ($datediff==1) ? $datediff.' month ago' : $datediff.' months ago';

                break;
            // If difference is greater than or equal to 365
            // days, return year. This will be incorrect if
            // for example, you call the function on the 28th April
            // 2008 passing in 29th April 2007. It will return
            // 1 year ago when in actual fact (yawn!) not quite
            // a year has gone by
            case(strtotime('-1 year', $dateto) >= $datefrom):
                $year_difference = 1;
                while (strtotime('-'.$year_difference.' year', $dateto) >= $datefrom)
                {
                    $year_difference++;
                }
               
                $datediff = $year_difference;
                $res = ($datediff==1) ? $datediff.' year ago' : $datediff.' years ago';
                break;
               
        }
        return $res; 
    }
 
	function diffAction()
	{
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        //$a = $this->datediff("2011/05/26 11:52:00", date("Y/m/d/ h:m:s"));
        $a = $this->ago("2011/05/26 11:52:00");

        echo '<pre>';
		print_r($a);
		echo '</pre>';
	}
	function diff2Action()
	{
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
		$q['check_in'] = date("Y/m/d/ h:m:s");
		$q['check_out'] = "2011/03/26 11:52:00";
		
		$start_day = (int) strftime("%d", strtotime($q['check_in']));
		$start_month = (int) strftime("%m", strtotime($q['check_in']));
		$start_year = (int) strftime("%Y", strtotime($q['check_in']));
		
		$end_day = (int) strftime("%d", strtotime($q['check_out']));
		$end_month = (int) strftime("%m", strtotime($q['check_out']));
		$end_year = (int) strftime("%Y", strtotime($q['check_out']));
		
		$diff = abs((mktime ( 0, 0, 0, $end_month, $end_day, $end_year) - mktime ( 0, 0, 0, $start_month, $start_day, $start_year))/(60*60*24));		
		print_r($diff);
	}
    function ftpAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

//        $ftp = new Pandamp_Lib_Ftp();
//        $ftp->ftp("173.203.109.70");
//        $ftp->rmAll("/var/www/html/vivanews2.php");
//
//      $ftp = ftp_connect("ftp.beta.hukumonline.com");
//      ftp_login($ftp, "root", "Beta12980");
//      ftp_put($ftp, "destfile.zip", "srcfile.zip", FTP_BINARY);
//      ftp_close($ftp);


$ftpurl = "ftp://zapatista:mydreams@beta.hukumonline.com";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ftpurl);
    curl_setopt($ch, CURLOPT_QUOTE, array("DELE /var/www/html/vivanews2.php"));

        $body = curl_exec($ch);
        $ret = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) != 0) throw new Exception("Service failure: HTTP request to server failed. " . curl_error($ch));
print_r($body.' code: '.$ret);
    }
    function decryptPasswordAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $obj = new Pandamp_Crypt_Password();
        echo $obj->decryptPassword('BTwENAA5A2QDOws+');
    }
    function generatePasswordAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        echo $this->randompassowrd();
    }
    function copydataAction()
    {
    	$this->_helper->layout->setLayout('layout-customer-migration');
    	
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$modelUserCopy = new App_Model_Db_Table_Copy_User();
    	$row = $modelUserCopy->fetchAll();
    	
    	$modelUser = new App_Model_Db_Table_User();
    	$modelUser->insert($row);
    }
    protected function randompassowrd($length = 8)
    {
        $password = "";

        $possible = "0123456789bcdfghjkmnpqrstvwxyz";

        $i = 0;

        while ($i < $length) {
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);

            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        return $password;
    }
}
