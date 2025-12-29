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

/*This class contains all the ldap constants*/
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Factory;
$userList = array();
Factory::getApplication()->set('moUserList', $userList);
class MoOkta
{
    private $baseUrl="";
	private $bearerToken="";
    private $userDetailsEndpoint="";
    private $mapUsername="";
    private $mapEmail="";
    private $mapName="";
    private array $moUserList = [];

    public function __construct(){

        $app = MoUserSyncUtility::moGetDetails('#__miniorange_user_sync_config');
		$mo_okta_application_attributes = !empty($app['mo_sync_configuration']) ? json_decode($app['mo_sync_configuration'], true) : "";
        $this->baseUrl = isset($mo_okta_application_attributes['mo_okta_base_url'])? $mo_okta_application_attributes['mo_okta_base_url']: "";
        $this->bearerToken = isset($mo_okta_application_attributes['mo_okta_bearer_token'])? $mo_okta_application_attributes['mo_okta_bearer_token']: "";
        $this->userDetailsEndpoint = $this->baseUrl.'/api/v1/users/';

        $mapDeatils = MoUserSyncUtility::moGetDetails('#__miniorange_sync_to_joomla');
        $this->mapUsername= isset($mapDeatils['moUsername'])	? $mapDeatils['moUsername']	: "";
        $this->mapEmail = isset($mapDeatils['moEmail'])	? $mapDeatils['moEmail']	: "";
        $this->mapName= isset($mapDeatils['moName'])	? $mapDeatils['moName']	: "";
        $this->moUserList = isset($mapDeatils['moUserList']) && !empty($mapDeatils['moUserList']) ? json_decode($mapDeatils['moUserList'],true) : array();

    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getbearerToken()
    {
        return $this->bearerToken;
    }
  
    public function getUserDetailsEndpoint()
    {
        return $this->userDetailsEndpoint;
    }

    public function getUserList(){
        return $this->moUserList;
    }

    public function getMapUsername()
    {
        return $this->mapUsername;
    }
    public function getMapName()
    {
        return $this->mapName;
    }
    public function getMapEmail()
    {
        return $this->mapEmail;
    }

    public static function getUserDetails($username)
    {
        //TEST CONFIGURATION
        $oktaDetails=new MoOkta();
        $url = $oktaDetails->getUserDetailsEndpoint().urlencode($username); 
        $header=array('Accept: application/json', 'Content-Type: application/json', 'Authorization: SSWS' .$oktaDetails->getbearerToken());
        $post=false;
        $post_fields = "";
        $testConfig = "";

        $userDetails = Authorization::moGetAccessToken($url,$header,$post,$post_fields);
        $testConfig = $oktaDetails->moOktaSyncGetRequest($userDetails);
        if(isset($testConfig['errorSummary'])){
            $testConfig['configuration_status'] = 'error';
        }else{
            $testConfig['configuration_status'] = 'successful';
        }
        return $testConfig;
    }

    public static function moOktaSyncGetRequest($user_details){
        
        $user_info = [];    
        foreach($user_details as $key=>$value){
            
            if(empty($value) || !isset($value)){continue;}
            else if(!is_array($value)){
            
                $user_info[$key] = $value;
            }
            else{
                  echo '';
            }
        }
        return $user_info;
    }

    public static function getAllUsers(){
     
        $oktaDetails=new MoOkta();
        $url = $oktaDetails->getUserDetailsEndpoint(); 
        $header=array('Accept: application/json', 'Content-Type: application/json', 'Authorization: SSWS' .$oktaDetails->getbearerToken());
        $post=false;
        $post_fields = "";
        $testConfig = "";

        $userDetails = Authorization::moGetAccessToken($url,$header,$post,$post_fields);
  
        if(isset($userDetails->errorCode)){
            return $userDetails;
        }else if(!empty($userDetails)){
          
            self::moOktaTestAttrMappingConfig($userDetails);
           
            $userList = Factory::getApplication()->get('moUserList');
            $database_name = '#__miniorange_sync_to_joomla';
            $updatefieldsarray = array(
                'moUserList' 	  	=> json_encode($userList),	
            );
            MoUserSyncUtility::moUpdateQuery($database_name, $updatefieldsarray);
        }else{

            $error = new stdClass();
            $error->errorCode ="MO 01";
            $error->errorSummary ="Invalid domain name entered";
            return $error;
        }
    }

     //FOR MAKING THE DETAILS AS PER OKTA STANDARDS
    public static function moOktaTestAttrMappingConfig($resourceOwnerDetails)
    {   	

        foreach ($resourceOwnerDetails as $data) {
            $id = isset($data->id) ? $data->id : 'ID not set';
            $firstName = isset($data->profile->email) ? $data->profile->email : 'Email not set';
        
            $users = array();
            array_push($users,array('Name'=>$firstName, 'Id' =>$id));
            $getAttributeName = Factory::getApplication()->get('moUserList');
            array_push($getAttributeName, $users);
            Factory::getApplication()->set('moUserList', $getAttributeName);
            
        }
 
    }

    public static function createUserInJoomla($username){

        $oktaDetails=new MoOkta();
        $url = $oktaDetails->getUserDetailsEndpoint().urlencode($username[0]["Name"]); 
        $header=array('Accept: application/json', 'Content-Type: application/json', 'Authorization: SSWS' .$oktaDetails->getbearerToken());
        $post=false;
        $post_fields = "";
        $testConfig = "";

        $userDetails = Authorization::moGetAccessToken($url,$header,$post,$post_fields);
      
        $usernameToSync = $username[0]['Name'];
        $db = Factory::getDBO();
        $query = $db->getQuery(true)
           ->select('*')
           ->from('#__users')
           ->where($db->quoteName('email') . ' = ' . $db->quote($usernameToSync) . 'OR' . $db->quoteName('username') . '=' . $db->quote($usernameToSync).'OR'.strtolower($db->quoteName('name')) . '=' . $db->quote(strtolower($usernameToSync)));
        $db->setQuery($query);
        $joomla_user = $db->loadAssocList();
      
        $user_in_joomla = isset($joomla_user) ? $joomla_user : '';
        
        $user_not_present = isset($userDetails->errorSummary) ? 'user_not_present' : 'user_present';
        if($user_not_present =='user_not_present'){
           return 'USER_NOT_PRESENT';
        }

        $moUserDetails = array();
        $moUserDetails['username'] = Authorization::getnestedattribute($userDetails, $oktaDetails->getMapUsername());
        $moUserDetails['email'] = Authorization::getnestedattribute($userDetails, $oktaDetails->getMapEmail());
        $moUserDetails['name'] = Authorization::getnestedattribute($userDetails, $oktaDetails->getMapName());

        if(Authorization::getnestedattribute($userDetails, '0.status') == 'ACTIVE' )
            $moUserDetails['enabled'] = 1;
        else
            $moUserDetails['enabled'] = 0;
          
        if(empty(trim($moUserDetails['username'])))
            return 'EMPTY_USERNAME';
            
        if(empty(trim($moUserDetails['email'])))
            return 'EMPTY_EMAIL';
            
        if(empty(trim($moUserDetails['name'])))
            return 'EMPTY_NAME';

        if(!filter_var($moUserDetails['email'], FILTER_VALIDATE_EMAIL))
            return 'INVALID_EMAIL_ATTRIBUTE';

        if(!$user_in_joomla){
            Authorization::moCreateUserInJoomla($moUserDetails,'Okta');
            return 'USER_CREATED';
        }
        else{
            return 'USER_ALREADY_PRESENT';
        }
    }

    

    public static function createUserInOkta($username){

        $oktaDetails=new MoOkta();
        $url = $oktaDetails->getBaseUrl().'/api/v1/users?activate=true';
        
        $db = Factory::getDBO();
        $query = $db->getQuery(true)
        ->select('*')
        ->from('#__users')
        ->where($db->quoteName('email') . ' = ' . $db->quote($username) . 'OR' . $db->quoteName('username') . '=' . $db->quote($username));
        $db->setQuery($query);
        $mo_okta_user_in_joomla = $db->loadAssocList();

        $userObject_test = [
            "profile" => 
            [
                'firstName' => $mo_okta_user_in_joomla[0]['username'],
                'lastName' => $mo_okta_user_in_joomla[0]['name'],
                'email' => $mo_okta_user_in_joomla[0]['email'],
                'login' => $mo_okta_user_in_joomla[0]['email'],
            ]
        ];

        $userObject = json_encode($userObject_test);    
        $response = Authorization::createUserInOkta($url,$userObject, $oktaDetails->getbearerToken());
        return $response; 

    }
}
?>