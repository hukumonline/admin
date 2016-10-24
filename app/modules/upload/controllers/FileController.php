<?php
/**
 * @author	2011-2018 Nihki Prihadi
 * @version $Id: FileController.php 1 2015-11-04 10:08Z $
 */

class Upload_FileController extends Zend_Controller_Action
{
	public function uploadAction()
	{
		$this->_helper->getHelper('viewRenderer')->setNoRender();
		$this->_helper->getHelper('layout')->disableLayout();
		
		$request = $this->getRequest();
		if (!$request->isPost()) {
			return;
		}
		
		$user = Zend_Auth::getInstance()->getIdentity();
		$userName = $user->username;
		
		$type = $request->getParam('type');
		$newguid = $request->getParam('guid');
		
		$cdn = Pandamp_Application::getOption('cdn');
		
		if ($type == 'file') {
			
			$dir  = $cdn['static']['dir']['files'];
			
			$adapter = new Zend_File_Transfer_Adapter_Http();
			$files = $adapter->getFileInfo();
			
			foreach ($files as $file => $info) {
				$guid = (new Pandamp_Core_Guid())->generateGuid();
				
				$path = implode(DS, array($newguid));
				
				Pandamp_Utility_File::createDirs($dir, $path);
				
				$adapter->setDestination($dir . DS . $path);
				
				$name = $adapter->getFileName($file,false);
				$fileku	= $dir . DS . $path . DS . strtoupper(str_replace(' ','_',$name));
				$adapter->addFilter('rename', ['overwrite' => true, 'target' => $fileku]);
					
				// file uploaded & is valid
				if (!$adapter->isUploaded($file)) continue;
				if (!$adapter->isValid($file)) continue;
				
				// receive the files into the user directory
				$adapter->receive($file);
				
				/*
				$baseUrl = $cdn['static']['url']['files'];
				if (isset($_SERVER['SCRIPT_NAME']) && ($pos = strripos($baseUrl, basename($_SERVER['SCRIPT_NAME']))) !== false) {
					$baseUrl = substr($baseUrl, 0, $pos);
				}
				$prefixUrl 		 = rtrim($baseUrl, '/') . '/' . $userName . '/' . date('Y') . '/' . date('m');
				$ret['original'] = array(
					'title' => strtoupper(str_replace(' ','_',$name)),
					'url'  => $prefixUrl . '/' . strtoupper(str_replace(' ','_',$name)),
					'size' => $adapter->getFileSize($file),
					'filetype' => $adapter->getMimeType($file),
					'type' => $type	
				);
				*/
				
				$ret['original'] = array(
					'id' => $guid,
					'title'  => strtoupper(str_replace(' ','_',$name)),
					'size' => $adapter->getFileSize($file),
					'filetype' => $adapter->getMimeType($file),
					'type' => $type
				);
				
			}
		}
		else 
		{
		
		/*$size = [
			'square' => 'crop_99_103',
			'thumbnail' => 'resize_100_53',
			'multimedia' => 'resize_245_169',
			'small' => 'resize_213_142', //klinik
			'headsmall' => 'resize_213_160', //header berita
			'crop' => 'crop_324_169', //ijt
			'cropnext' => 'crop_325_183', //nextevent
			'mainhead' => 'resize_462_309', //utama
			'medium' => 'resize_646_431'
		];*/
		
		$tool = 'gd';
		
		$size = new Zend_Config_Ini(APPLICATION_PATH . '/configs/image.ini','size');
		
		$sizes 	= array();
		foreach ($size->toArray() as $key => $value) {
			list($method, $width, $height) = explode('_', $value);
			$sizes[$key] = array('method' => $method, 'width' => $width, 'height' => $height);
		}
		
		
		
		/**
		 * Prepare folders
		 */
		//$dir  = $cdn['static']['dir']['images'] . DS . 'upload';
		$dir  = $cdn['static']['dir']['images'];
		//$path = implode(DS, array($userName, date('Y'), date('m'), date('d')));
		$path = implode(DS, array($newguid));
		Pandamp_Utility_File::createDirs($dir, $path);
		
		/**
		 * Upload file
		 */
		$adapter = new Zend_File_Transfer_Adapter_Http();
		
		$adapter->setDestination($dir . DS . $path);
		$adapter->addValidator('Extension', false, 'jpg,png,gif');
		$adapter->addValidator('Size', false, 5242880);
		
		$files = $adapter->getFileInfo();
		foreach ($files as $file => $info) {
			$name = $adapter->getFileName($file);
			
			$ext 	   = explode('.', $name);
			$extension = $ext[count($ext) - 1];
			$extension = strtolower($extension);
			//$fileName  = uniqid();
			$fileName  = (new Pandamp_Core_Guid())->generateGuid();
			$fileku	   = $dir . DS . $path . DS . $fileName . '.' . $extension;
			
			$adapter->addFilter('rename', $fileku);
			
			// file uploaded & is valid
			if (!$adapter->isUploaded($file)) continue;
			if (!$adapter->isValid($file)) continue;
			
			// receive the files into the user directory
			$adapter->receive($file); // this has to be on top
			
			/**
			 * Generate thumbnails
			 */
			$thumbnailSizes = array_keys($sizes);
			
			$service = null;
			$service = new Pandamp_Image_GD();
			
			/**
			 * Remove script filename from base URL
			 */
			$baseUrl = $cdn['static']['url']['images'];
			
			/*
			if (isset($_SERVER['SCRIPT_NAME']) && ($pos = strripos($baseUrl, basename($_SERVER['SCRIPT_NAME']))) !== false) {
				$baseUrl = substr($baseUrl, 0, $pos);
			}
			$prefixUrl 		 = rtrim($baseUrl, '/') . '/upload/' . $userName . '/' . date('Y') . '/' . date('m') . '/' . date('d');
			*/
			
			$prefixUrl 		 = $baseUrl . '/' . $newguid;
			$ret['original'] = array(
				'id' => $fileName,	
				'title' => $fileName . '.' . $extension,
				'url' => $prefixUrl . '/' . $fileName . '.' . $extension,
				'size' => null,
				'filetype' => $adapter->getMimeType($file),
				'type' => $type
			);
			
			if ($thumbnailSizes) {
				$service->setFile($fileku);
				$ret['original']['size'] = $service->getWidth() . ' x ' . $service->getHeight();
				foreach ($thumbnailSizes as $s) {
					$service->setFile($fileku);
					$method = $sizes[$s]['method'];
					$width 	= $sizes[$s]['width'];
					$height = $sizes[$s]['height'];
					
					$f 		 = $s . '_' . $fileName . '.' . $extension;
					$newFile = $dir . DS . $path . DS . $f;
					
					/**
					 * Create thumbnail
					 */
					switch ($method) {
						case 'resize':
							$service->resizeLimit($newFile, $width, $height);
							break;
						case 'crop':
							$service->crop($newFile, $width, $height);
							break;
					}
					
					/*$ret[$s] = array(
						'title' => 	$f,
						'url'  => $prefixUrl . '/' . $f,
						'size' => $width . ' x ' . $height,
						'filetype' => $adapter->getMimeType($file),
						'type' => $type
					);*/
				}
			}
			
						
		}
		
		}
		
		
		/**
		 * Return the reponse
		 */
		$ret = Zend_Json::encode($ret);
		$this->getResponse()->setBody($ret);
		
	}
}