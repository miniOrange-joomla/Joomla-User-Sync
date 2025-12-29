<?php
/**
 * @package     Joomla.Component
 * @subpackage  com_miniorange_usersync
 *
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
class MoUserSyncCustomer{
	
	public $email;
	public $phone;
	public $customerKey;
	public $transactionId;
	
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	
	function moSubmitContactUs( $q_email, $q_phone, $query) {
			
		$url = 'https://login.xecurify.com/moas/api/notify/send';
		$ch = curl_init($url);
		$customerKey = "16555";
		$apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
		
		$currentTimeInMillis = round(microtime(true) * 1000);
		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);
		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
		$authorizationHeader = "Authorization: " . $hashValue;
		$fromEmail = $q_email;
		$phpVersion = phpversion();
		$jVersion = new Version;
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = MoUserSyncUtility::moGetPluginVersion();
		$subject = "Query for MiniOrange Joomla API Based User Provisioning Free - ".$fromEmail;
		
		$currentUserEmail 	= Factory::getUser();
		$adminEmail         = $currentUserEmail->email;
		$pluginInfo = '['.$moPluginVersion.' | PHP ' . $phpVersion.' | System OS '.$moSystemOS.' ] ';
		$query = '[MiniOrange Joomla User Sync Free | '.$phpVersion. ' | '.$jCmsVersion.' | '.$moPluginVersion.'] ' . $query;
		$content = '<div >Hello, <br><br>
					<strong>Company</strong> :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
					<strong>Phone Number</strong> :'.$q_phone.'<br><br>
					<strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br>
					<b>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></b><br><br>
					<b>Query</b>: '.$query. '</b></div>';

		$fields = array(
			'customerKey' => $customerKey,
			'sendEmail' => true,
			'email' => array(
				'customerKey' => $customerKey,
				'fromEmail' => $fromEmail,
				'fromName' => 'miniOrange',
				'toEmail' => 'joomlasupport@xecurify.com',
				'toName' => 'joomlasupport@xecurify.com',
				'subject' => $subject,
				'content' => $content
			),
		);
		$field_string = json_encode($fields);
		$http_header_array = array( 'Content-Type: application/json', $customerKeyHeader, $timestampHeader, $authorizationHeader);
		return self::mo_post_curl($url,$field_string,$http_header_array);
	}

	public static function mo_user_sync_submit_feedback_form($email,$phone,$query,$cause)
	{
	
        $url='https://login.xecurify.com/moas/api/notify/send';       
        $customerKey="16555";
		$apiKey="fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

        $currentTimeInMillis=round(microtime(true) * 1000);
        $stringToHash=$customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue=hash("sha512", $stringToHash);
        $customerKeyHeader="Customer-Key: " . $customerKey;
        $timestampHeader="Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader="Authorization: " . $hashValue;
        $fromEmail=$email;
        $subject="Feedback for miniOrange Joomla API Based User Provisioning [Free] ";
        $currentUserEmail=Factory::getUser();
        $adminEmail=$currentUserEmail->email;
         $query1="MiniOrange Joomla API Based User provisioning [Free]:";
         $content='<div >Hello, <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>Phone Number :'.$phone.'<br><br><b>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></b><br><br><strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br><b>Plugin Deactivated: '.$query1. '</b><br><br><b>Reason: ' .$query. '</b></div>';

        $fields=array(
            'customerKey'=> $customerKey,
            'sendEmail'=> true,
            'email'=> array(
                'customerKey'=> $customerKey,
                'fromEmail'=> $fromEmail,
                'fromName'=> 'miniOrange',
                'toEmail'=> 'joomlasupport@xecurify.com',
                'toName'=> 'joomlasupport@xecurify.com',
                'subject'=> $subject,
                'content'=> $content
            ),
        );
        
		$field_string=json_encode($fields);
		$http_header_array=array( 'Content-Type: application/json',$customerKeyHeader, $timestampHeader, $authorizationHeader);
		return self::mo_post_curl($url,$field_string,$http_header_array);
	}

	//POST CALL FUNCTIONS
	public static function mo_post_curl($url, $fields, $http_header_array){
		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );   
		
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $http_header_array );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields);
			
		$content = curl_exec( $ch );
		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		return false;
		}
		curl_close($ch);

		return $content;
	}
}?>