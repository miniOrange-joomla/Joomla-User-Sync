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

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
class Authorization{
    
    //TO GET ACCESS TOKEN 
    public static function moGetAccessToken($url,$header,$post,$post_fields){
        
        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if($post == true){
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        
        $content = curl_exec($ch);
        curl_close($ch);
        $content = json_decode($content);
        return $content;
    }

    //TO GET USER DETAILS
    public static function moGetDetails($user_info_url, $agrs){
        $ch = curl_init($user_info_url);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$agrs->access_token));
        $content = curl_exec($ch);

        curl_close($ch);
        return $content;

    }

    //FOR AZURE AND KEYCLOAK
    public static function moGetUserDetails($user_info_url, $agrs){
        
        $user_info = [];
        $user_details = self::moGetDetails($user_info_url, $agrs);
        $user_details = json_decode($user_details);
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


    //TO GET ATTRIBUTES
    public static function getnestedattribute($data, $path){
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (is_object($data) && property_exists($data, $key)) {
                $data = $data->$key;
            } elseif (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            }else{
                $data = "";
            }

        }
        return $data;
    }

    //TO CREATE USER IN THE AD
    public static function createUserInProvider($url,$agrs,$body){

        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$agrs->access_token));
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body);
    
        $content = curl_exec( $ch );
        curl_close($ch);
        return $content;        
    }

    public static function moGetAllUsers($endpoints, $agrs){

        $ch = curl_init($endpoints);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$agrs->access_token));
        $content = curl_exec($ch);

        curl_close($ch);
        return $content;
    }

    // public static function moCreateUserInJoomla($moUserDetails, $appName){
    //     $date = date('d-m-y h:i:s');
    //     $db = Factory::getDbo();
    //     $query = $db->getQuery(true);
    //     $fields = array(
        
    //         $db->quoteName('email') . ' = ' . $db->quote($moUserDetails['email']),
    //         $db->quoteName('name') . ' = ' . $db->quote($moUserDetails['name']),
    //         $db->quoteName('username') . ' = ' . $db->quote($moUserDetails['username']),
    //         $db->quoteName('registerDate') . ' = ' . $db->quote($date),
    //         $db->quoteName('params') . ' = ' . $db->quote(""),
    //         $db->quoteName('block') . ' = ' . $db->quote($moUserDetails['enabled']),
    //     );

    //     $query->insert($db->quoteName('#__users'))->set($fields);
    //     $db->setQuery($query);
    //     $db->execute();

    // }

    public static function moCreateUserInJoomla($moUserDetails, $appName)
    {
        $date = date('Y-m-d H:i:s');
        $db = Factory::getDbo();

        // Get default user group from Joomla configuration
        $config = Factory::getConfig();
        $defaultGroup = $config->get('new_usertype', 2);

        // Create a new user object
        $user = new Joomla\CMS\User\User;

        $user->name = $moUserDetails['name'];
        $user->username = $moUserDetails['username'];
        $user->email = $moUserDetails['email'];
        $user->registerDate = $date;
        $user->block = 0; // enabled
        $user->sendEmail = 0;
        $user->params = '{}';

        if (!$user->bind($data)) {
            throw new Exception($user->getError());
        }

        if (!$user->save()) {
            throw new Exception($user->getError());
        }
        
        // Add user to default group
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__user_usergroup_map'))
            ->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')))
            ->values($db->quote($user->id) . ', ' . $db->quote($defaultGroup));
        $db->setQuery($query);
        $db->execute();

        return $user->id;
    }


    //CREATE USER IN OKTA
    public static function createUserInOkta($endpoint,$body, $agrs){
        $ch = curl_init($endpoint);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json', 'Authorization: SSWS ' .$agrs));
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $body);

        $content = curl_exec( $ch );
        $content = json_decode($content);
        curl_close($ch);
        return $content;
    }

    public static function createUserInAWSCognito($endpoint, $headers, $post_fields) {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
    
        return [
            'response' => $response,
            'httpCode' => $httpCode,
            'error' => $error
        ];
    }
}