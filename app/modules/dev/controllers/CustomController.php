<?php

/**
 * Description of customController
 *
 * @author nihki <nihki@madaniyah.com>
 */
require_once("vendor/autoload.php");

use \PhpOffice\PhpWord\IOFactory,
	\Smalot\PdfParser\Parser;
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
	
	const NAME_ORDERQUEUE = 'job_queue';
	function addjobsAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$options = array(
			'name'          => self::NAME_ORDERQUEUE,
			'driverOptions' => array(
				'host'      => 'localhost',
				'port'      => '3306',
				'username'  => 'root',
				'password'  => '',
				'dbname'    => 'sjalocal',
				'type'      => 'pdo_mysql'
			)
		);
		
		$queue = new Pandamp_Job_Queue('Db', $options);
		
		$params = array('example'=>'param');
		for($i=0;$i<10;$i++)
		{
			$params['requence to show differences']=$i;
			$params['time to show differences']=time();
			$queue->addJob('Pandamp_Job_Example',
				$params,
					false);
		}
		
	}
	
	function runjobsAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$options = array(
				'name'          => self::NAME_ORDERQUEUE,
				'driverOptions' => array(
						'host'      => 'localhost',
						'port'      => '3306',
						'username'  => 'root',
						'password'  => '',
						'dbname'    => 'sjalocal',
						'type'      => 'pdo_mysql'
				)
		);
		
		$queue = new Pandamp_Job_Queue('Db', $options);
		$queue->runJobs();
	}
	
	public function docAction()
	{
		$this->_helper->layout->setLayout('layout-dms-uploader');
		
		//$this->_helper->layout->disableLayout();
		//$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$request = $this->getRequest();
		
// 		$source = ROOT_DIR . "/data/PP_NO_31_1995.DOC";
// 		$source = ROOT_DIR . "/data/PERPRES_NO_77_2007.DOC";
		
		//\PhpOffice\PhpWord\Settings::loadConfig(CONFIG_PATH.'/phpword.ini');
		//$phpWord = \PhpOffice\PhpWord\IOFactory::load($source,"MsDoc");
		//echo $this->write($phpWord, basename(__FILE__, '.php'), ['HTML' => 'html']);
		
// 		Pandamp_Debug::manager($this->read_doc($source));
		if ($request->isPost()) {
			$registry = Zend_Registry::getInstance();
			$files = $registry->get('files');
			
		$regType = [
			'peraturan pemerintah',
			'peraturan presiden',
			'undang-undang',
			'peraturan menteri'
		];
// 		$content = $this->parseWord($source);
// 		$lines = file($source);
// 		$text = strtolower($lines[1]);
// 		$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$text);
// 		if ( file_exists($source) ) {

		$pdf = new PdfParser();
		$string = $pdf->parseFile($files['uploadedFile1']['tmp_name']);
		$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$string);
		Pandamp_Debug::manager($outtext);
				
			if ( ($fh = fopen($files['uploadedFile1']['tmp_name'], 'r')) !== false ) {
		
				$headers = fread($fh, 0xA00);
		
				$n1 = ( ord($headers[0x21C]) - 1 );
		
				$n2 = ( ( ord($headers[0x21D]) - 8 ) * 256 );
		
				$n3 = ( ( ord($headers[0x21E]) * 256 ) * 256 );
		
				$n4 = ( ( ( ord($headers[0x21F]) * 256 ) * 256 ) * 256 );
		
		
				$textLength = ($n1 + $n2 + $n3 + $n4);
		
				$extracted_plaintext = fread($fh, $textLength);
		
				//echo nl2br($extracted_plaintext);die;
				$c = nl2br($extracted_plaintext);
				//print_r($this->extract_emails_from($extracted_plaintext));die;
				
				$fp = fopen("php://memory", 'r+');
				fputs($fp, $c);
				rewind($fp);
				while($line = fgets($fp)){
					/*foreach(preg_split("/((\r?\n)|(\r\n?))/", $line) as $l){
					    echo $l."\n";die;
					}*/ 
					$arr = preg_split("/((\r?\n)|(\r\n?))/", $line);
					//Pandamp_Debug::manager($arr);
					$result = array_filter($regType, create_function('$e','return strstr("'.strtolower($arr[0]).'", $e);'));
					if ($result) {	
						$txt = array_values($result);
						$txt = ucwords($txt[0])."\n";
					}
					
					$txt .= $arr[1];
				}
				fclose($fp);
				
				
				$this->view->assign('test',$txt);
			}
		
// 		}
		
		}
	}
	
	// open pdf in browser
	function readPdf($file1)
	{
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="'.$file1.'"');
		header('Accept-Ranges: bytes');
		$f = @readfile($file1);
		
		return $f;
	}
	
	public function LoadData($file) {
		// Read file lines
		$lines = file($file);
		$data = array();
		foreach($lines as $line) {
			$data[] = explode(';', chop($line));
		}
		return $data;
	}
	
	function extract_emails_from($string) {
		preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);
		return $matches[0];
	}
	
	private function read_doc($filename) {
		$line_array = array();
		$fileHandle = fopen( $filename, "r" );
		$line       = @fread( $fileHandle, filesize( $filename ) );
		$lines      = explode( chr( 0x0D ), $line );
		$outtext    = "";
		foreach ( $lines as $thisline ) {
			$pos = strpos( $thisline, chr( 0x00 ) );
			if (  $pos !== false )  {
	
			} else {
				$line_array[] = preg_replace( "/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/", "", $thisline );
	
			}
		}
	
		return implode("\n",$line_array);
	}
	
	function parseWord($userDoc)
	{
		$fileHandle = fopen($userDoc, "r");
		$word_text = @fread($fileHandle, filesize($userDoc));
		$line = "";
		$tam = filesize($userDoc);
		$nulos = 0;
		$caracteres = 0;
		for($i=1536; $i<$tam; $i++)
		{
		$line .= $word_text[$i];
	
		if( $word_text[$i] == 0)
		{
		$nulos++;
		}
		else
		{
		$nulos=0;
			$caracteres++;
		}
	
		if( $nulos>1996)
		{
		break;
		}
		}
	
			//echo $caracteres;
	
			$lines = explode(chr(0x0D),$line);
			//$outtext = "<pre>";
	
				$outtext = "";
				foreach($lines as $thisline)
				{
				$tam = strlen($thisline);
				if( !$tam )
				{
				continue;
				}
	
				$new_line = "";
				for($i=0; $i<$tam; $i++)
				{
				$onechar = $thisline[$i];
				if( $onechar > chr(240) )
				{
				continue;
				}
	
					if( $onechar >= chr(0x20) )
					{
					$caracteres++;
					$new_line .= $onechar;
				}
	
				if( $onechar == chr(0x14) )
				{
				$new_line .= "</a>";
					}
	
					if( $onechar == chr(0x07) )
					{
						$new_line .= "\t";
						if( isset($thisline[$i+1]) )
						{
						if( $thisline[$i+1] == chr(0x07) )
						{
						$new_line .= "\n";
						}
						}
						}
						}
						//troca por hiperlink
						$new_line = str_replace("HYPERLINK" ,"<a href=",$new_line);
						$new_line = str_replace("\o" ,">",$new_line);
						$new_line .= "\n";
	
							//link de imagens
									$new_line = str_replace("INCLUDEPICTURE" ,"<br><img src=",$new_line);
										$new_line = str_replace("\*" ,"><br>",$new_line);
										$new_line = str_replace("MERGEFORMATINET" ,"",$new_line);
	
	
										$outtext .= nl2br($new_line);
					}
					//$outtext = preg_replace( "/^'|[^A-Za-z0-9\'-]|'$/", "", $outtext );
					$outtext = preg_replace( "/[^(\x20-\x7f)]*/s", "", $outtext );
					return $outtext;
	}
	
	function write($phpWord, $filename, $writers)
	{
		$result = '';
	
		// Write documents
		foreach ($writers as $format => $extension) {
			$result .= date('H:i:s') . " Write to {$format} format";
			if (null !== $extension) {
				$targetFile = ROOT_DIR . "/data/{$filename}.{$extension}";
				$phpWord->save($targetFile, $format);
			} else {
				$result .= ' ... NOT DONE!';
			}
			$result .= "<br>";
		}
	
// 		$result .= getEndingNotes($writers);
	
		return $result;
	}
	
	public function testAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		//$slug = "http://www.hukumonline.com/berita/baca/lt564de15027f4d/jalan-keluar-bagi-advokat-dalam-implementasikan-pp-no-43-tahun-2015";
		
		// hasilnya: -berita-baca-lt564de15027f4d-jalan-keluar-bagi-advokat-dalam-implementasikan-pp-no-43-tahun-2015
		//echo $this->getSlug($slug,['http://www.hukumonline.com']);
		
		// hasilnya: berita/baca/lt564de15027f4d/jalan-keluar-bagi-advokat-dalam-implementasikan-pp-no-43-tahun-2015
		//echo trim(parse_url($slug, PHP_URL_PATH), '/');
		
		// hasilnya: jalan-keluar-bagi-advokat-dalam-implementasikan-pp-no-43-tahun-2015
		//echo basename($slug);
		
		// Find first gap in array
		$a = array('1','3','5','7');
		//$a[] = '2';
		//sort($a);
		$start = array_shift($a);
		
		foreach($a as $v){
			if ($start + 1 != $v) {
				$missing = $start + 1;
				break;
			}
		
			$start = $v;
		}
		
		// hasil:4
		if (isset($missing))
			echo $missing;
		else
			echo $start + 1; // yang terakhir +1
	}
	
	public function parseAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$url = "http://m.hukumonline.com/pusatdata/detail/lt4f796689194c0/node/537/pp-no-11-tahun-2008-perubahan-kelima-atas-peraturan-pemerintah-nomor-8-tahun-2000-tentang-peraturangaji-hakim-peradilan-umum,-peradilan-tata-usaha-negara,-dan-peradilan-agama";
		$url = pathinfo($url);
		$url = $url['dirname'];
		//$url = basename($url);
		
		print_r($url);
	}
	
	public function referralAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$db = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application-cli.ini','mongodb');
		Shanty_Mongo::addConnections($db);
		
		$ref = App_Model_Mongodb_RequestLog::referral();
		
		print_r($ref);
	}
	
	public function testfileAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$recoveredData = file_get_contents(ROOT_DIR.DS.'temp'.DS.'datashorturl.2211151032.test1');
		$recoveredArray = unserialize($recoveredData);
		$start = array_shift($recoveredArray);
		
		foreach($recoveredArray as $v){
			if ($start + 1 != $v) {
				$missing = $start + 1;
				break;
			}
		
			$start = $v;
		}
		
		echo $missing;
	}
	
	function getSlug($str, $replace=array(), $delimiter='-') {
		setlocale(LC_ALL, 'en_US.UTF8');
		//
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean=strip_tags($clean);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
		return $clean;
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

        $userDb = new App_Model_Db_Table_User();
        $users = $userDb->fetchAll();
        foreach ($users as $user) {
        	$obj = new Pandamp_Crypt_Password();
        	$passwd = $obj->decryptPassword($user->password);
        	if ($passwd == 'g00dPa$$w0rD') {
        		echo $user->kopel.'<br>';
        	}
        }
        
        //echo md5('SolrRocks');
        //$obj = new Pandamp_Crypt_Password();
        //echo $obj->decryptPassword('VTIDb1o8Bz0LaApsUndSJgtoB3QDJ1x2');
        /*for($i = 1; $i <=10; $i++) {
        
        	$plainPassword = $this->generateRandomString();
        	$cryptedPassword = $obj->encryptPassword($plainPassword);
        	$deCryptedPassword = $obj->decryptPassword($cryptedPassword);
        	echo $cryptedPassword.' '.$deCryptedPassword.' plainnya: '.$plainPassword."<br>";
        }*/
        
    }
    function generateRandomString($length = 10) {
    	return substr(str_shuffle("_!'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
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
    	$rowset = $modelUserCopy->fetchAll();
    	
    	
    	foreach ($rowset as $row)
    	{
	    	$modelUser = new App_Model_Db_Table_User();
	    	$rowUser = $modelUser->fetchNew();
	    	$rowUser->kopel				= $row->kopel;
	    	$rowUser->username			= $row->username;
	    	$rowUser->password			= $row->password;
	    	$rowUser->fullName			= $row->fullName;
	    	$rowUser->birthday			= $row->birthday;
	    	$rowUser->phone				= $row->phone;
	    	$rowUser->fax				= $row->fax;
	    	$rowUser->gender			= $row->gender;
	    	$rowUser->email				= $row->email;
	    	$rowUser->openId			= $row->openId;
	    	$rowUser->company			= $row->company;
	    	$rowUser->address			= $row->address;
	    	$rowUser->city				= $row->city;
	    	$rowUser->state				= $row->state;
	    	$rowUser->countryId			= $row->countryId;
	    	$rowUser->zip				= $row->zip;
	    	$rowUser->indexCol			= $row->indexCol;
	    	$rowUser->picture			= $row->picture;
	    	$rowUser->newArticle		= $row->newArticle;
	    	$rowUser->weeklyList		= $row->weeklyList;
	    	$rowUser->monthlyList		= $row->monthlyList;
	    	$rowUser->packageId			= $row->packageId;
	    	$rowUser->promotionId		= $row->promotionId;
	    	$rowUser->educationId		= $row->educationId;
	    	$rowUser->expenseId			= $row->expenseId;
	    	$rowUser->paymentId			= $row->paymentId;
	    	$rowUser->businessTypeId	= $row->businessTypeId;
	    	$rowUser->periodeId			= $row->periodeId;
	    	$rowUser->activationDate	= $row->activationDate;
	    	$rowUser->isEmailSent		= $row->isEmailSent;
	    	$rowUser->isEmailSentOver	= $row->isEmailSentOver;
	    	$rowUser->createdDate		= $row->createdDate;
	    	$rowUser->createdBy			= $row->createdBy;
	    	$rowUser->modifiedDate		= $row->modifiedDate;
	    	$rowUser->modifiedBy		= $row->modifiedBy; 
	    	$rowUser->isActive			= $row->isActive;
	    	$rowUser->isContact			= $row->isContact;
    		
	    	$id = $rowUser->save();
	    	
    	}
    	
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

/**
 * @file
* Class PdfParser
*
* @author : Sebastien MALOT <sebastien@malot.fr>
* @date : 2013-08-08
*
* References :
* - http://www.mactech.com/articles/mactech/Vol.15/15.09/PDFIntro/index.html
* - http://framework.zend.com/issues/secure/attachment/12512/Pdf.php
* - http://www.php.net/manual/en/ref.pdf.php#74211
*/
class PdfParser
{
	/**
	 * Parse PDF file
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function parseFile($filename)
	{
		$content = file_get_contents($filename);

		return self::extractText($content);
	}

	/**
	 * Parse PDF content
	 *
	 * @param string $content
	 * @return string
	 */
	public static function parseContent($content)
	{
		return self::extractText($content);
	}

	/**
	 * Convert a PDF into text.
	 *
	 * @param string $filename The filename to extract the data from.
	 * @return string The extracted text from the PDF
	 */
	protected static function extractText($data)
	{
		/**
		 * Split apart the PDF document into sections. We will address each
		 * section separately.
		 */
		$a_obj    = self::getDataArray($data, 'obj', 'endobj');
		$j        = 0;
		$a_chunks = array();

		/**
		 * Attempt to extract each part of the PDF document into a 'filter'
		 * element and a 'data' element. This can then be used to decode the
		 * data.
		*/
		foreach ($a_obj as $obj) {
			$a_filter = self::getDataArray($obj, '<<', '>>');

			if (is_array($a_filter) && isset($a_filter[0])) {
				$a_chunks[$j]['filter'] = $a_filter[0];
				$a_data = self::getDataArray($obj, 'stream', 'endstream');

				if (is_array($a_data) && isset($a_data[0])) {
					$a_chunks[$j]['data'] = trim(substr($a_data[0], strlen('stream'), strlen($a_data[0]) - strlen('stream') - strlen('endstream')));
				}

				$j++;
			}
		}

		$result_data = null;

		// decode the chunks
		foreach ($a_chunks as $chunk) {
			// Look at each chunk decide if we can decode it by looking at the contents of the filter
			if (isset($chunk['data'])) {

				// look at the filter to find out which encoding has been used
				if (strpos($chunk['filter'], 'FlateDecode') !== false) {
					// Use gzuncompress but suppress error messages.
					$data =@ gzuncompress($chunk['data']);
				} else {
					$data = $chunk['data'];
				}

				if (trim($data) != '') {
					// If we got data then attempt to extract it.
					$result_data .= ' ' . self::extractTextElements($data);
				}
			}
		}

		/**
		 * Make sure we don't have large blocks of white space before and after
		 * our string. Also extract alphanumerical information to reduce
		 * redundant data.
		 */
		if (trim($result_data) == '') {
			return null;
		} else {
			// Optimize hyphened words
// 			$result_data = preg_replace('/\s*-[\r\n]+\s*/', '', $result_data);
// 			$result_data = preg_replace('/\s+/', ' ', $result_data);
			$result_data = preg_split("/((\r?\n)|(\r\n?))/", $result_data);
// 			$result_data = preg_replace("/\s*-[[\r\n]|[\r|\n]]+\s*/",'',$result_data);

			return $result_data;
		}
	}

	protected static function extractTextElements($content)
	{
		if (strpos($content, '/CIDInit') === 0) {
			return '';
		}

		$text  = '';
		$lines = explode("\n", $content);

		foreach ($lines as $line) {
			$line = trim($line);
			$matches = array();

			// Parse each lines to extract command and operator values
			if (preg_match('/^(?<command>.*[\)\] ])(?<operator>[a-z]+[\*]?)$/i', $line, $matches)) {
				$command = trim($matches['command']);

				// Convert octal encoding
				$found_octal_values = array();
				preg_match_all('/\\\\([0-9]{3})/', $command, $found_octal_values);

				foreach($found_octal_values[0] as $value) {
					$octal = substr($value, 1);

					if (intval($octal) < 40) {
						// Skips non printable chars
						$command = str_replace($value, '', $command);
					} else {
						$command = str_replace($value, chr(octdec($octal)), $command);
					}
				}
				// Removes encoded new lines, tabs, ...
				$command = preg_replace('/\\\\[\r\n]/', '', $command);
				$command = preg_replace('/\\\\[rnftb ]/', ' ', $command);
				// Force UTF-8 charset
				$encoding = mb_detect_encoding($command, array('ASCII', 'UTF-8', 'Windows-1252', 'ISO-8859-1'));
				if (strtoupper($encoding) != 'UTF-8') {
					if ($decoded = @iconv('CP1252', 'UTF-8//TRANSLIT//IGNORE', $command)) {
						$command = $decoded;
					}
				}
				// Removes leading spaces
				$operator = trim($matches['operator']);
			} else {
				$command = $line;
				$operator = '';
			}

			// Handle main operators
			switch ($operator) {
				// Set character spacing.
				case 'Tc':
					break;

					// Move text current point.
				case 'Td':
					$values = explode(' ', $command);
					$y = array_pop($values);
					$x = array_pop($values);
					if ($x > 0) {
						$text .= ' ';
					}
					if ($y < 0) {
						$text .= ' ';
					}
					break;

					// Move text current point and set leading.
				case 'TD':
					$values = explode(' ', $command);
					$y = array_pop($values);
					if ($y < 0) {
						$text .= "\n";
					}
					break;

					// Set font name and size.
				case 'Tf':
					$text.= ' ';
					break;

					// Display text, allowing individual character positioning
				case 'TJ':
					$start = mb_strpos($command, '[', null, 'UTF-8') + 1;
					$end   = mb_strrpos($command, ']', null, 'UTF-8');
					$text.= self::parseTextCommand(mb_substr($command, $start, $end - $start, 'UTF-8'));
					break;

					// Display text.
				case 'Tj':
					$start = mb_strpos($command, '(', null, 'UTF-8') + 1;
					$end   = mb_strrpos($command, ')', null, 'UTF-8');
					$text.= mb_substr($command, $start, $end - $start, 'UTF-8'); // Removes round brackets
					break;

					// Set leading.
				case 'TL':

					// Set text matrix.
				case 'Tm':
					//          $text.= ' ';
					break;

					// Set text rendering mode.
				case 'Tr':
					break;

					// Set super/subscripting text rise.
				case 'Ts':
					break;

					// Set text spacing.
				case 'Tw':
					break;

					// Set horizontal scaling.
				case 'Tz':
					break;

					// Move to start of next line.
				case 'T*':
					$text.= "\n";
					break;

					// Internal use
				case 'g':
				case 'gs':
				case 're':
				case 'f':
					// Begin text
				case 'BT':
					// End text
				case 'ET':
					break;

				case '':
					break;

				default:
			}
		}

		$text = str_replace(array('\\(', '\\)'), array('(', ')'), $text);

		return $text;
	}

	/**
	 * Strip out the text from a small chunk of data.
	 *
	 * @param string $text
	 * @param int $font_size Currently not used
	 *
	 * @return string
	 */
	protected static function parseTextCommand($text, $font_size = 0) {

		$result = '';
		$cur_start_pos = 0;

		while (($cur_start_text = mb_strpos($text, '(', $cur_start_pos, 'UTF-8')) !== false) {
			// New text element found
			if ($cur_start_text - $cur_start_pos > 8) {
				$spacing = ' ';
			} else {
				$spacing_size = mb_substr($text, $cur_start_pos, $cur_start_text - $cur_start_pos, 'UTF-8');

				if ($spacing_size < -50) {
					$spacing = ' ';
				} else {
					$spacing = '';
				}
			}
			$cur_start_text++;

			$start_search_end = $cur_start_text;
			while (($cur_start_pos = mb_strpos($text, ')', $start_search_end, 'UTF-8')) !== false) {
				if (mb_substr($text, $cur_start_pos - 1, 1, 'UTF-8') != '\\') {
					break;
				}
				$start_search_end = $cur_start_pos + 1;
			}

			// something wrong happened
			if ($cur_start_pos === false) {
				break;
			}

			// Add to result
			$result .= $spacing . mb_substr($text, $cur_start_text, $cur_start_pos - $cur_start_text, 'UTF-8');
			$cur_start_pos++;
		}

		return $result;
	}

	/**
	 * Convert a section of data into an array, separated by the start and end words.
	 *
	 * @param  string $data       The data.
	 * @param  string $start_word The start of each section of data.
	 * @param  string $end_word   The end of each section of data.
	 * @return array              The array of data.
	 */
	protected static function getDataArray($data, $start_word, $end_word)
	{
		$start     = 0;
		$end       = 0;
		$a_results = array();

		while ($start !== false && $end !== false) {
			$start = strpos($data, $start_word, $end);
			$end   = strpos($data, $end_word, $start);

			if ($end !== false && $start !== false) {
				// data is between start and end
				$a_results[] = substr($data, $start, $end - $start + strlen($end_word));
			}
		}

		return $a_results;
	}
}
