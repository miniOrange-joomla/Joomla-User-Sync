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
use Joomla\Database\DatabaseInterface;

class MoUserSyncUtility{
	
	public static function moGetDetails(String $tablename){
		$db = self::moGetDatabase();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName($tablename));
		$query->where($db->quoteName('id')." = 1");
 
		$db->setQuery($query);
		$customer_details = $db->loadAssoc();
		return $customer_details;
	}

    public static function moGetDatabase()
    {
        // Joomla 4+
        if (class_exists(DatabaseInterface::class) && method_exists(Factory::class, 'getContainer')) {
            return Factory::getContainer()->get(DatabaseInterface::class);
        }

        // Joomla 3 fallback
        return Factory::getDbo();
    }

	public static function moUpdateQuery($database_name, $updatefieldsarray){

        $db = self::moGetDatabase();
		$query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value)
        {
            $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }
		$query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $db->execute();
		
    }

	public static function moCheckEmptyOrNull( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	public static function moGetPluginVersion()
	{
	$db = self::moGetDatabase();
	$dbQuery = $db->getQuery(true)
	->select('manifest_cache')
	->from($db->quoteName('#__extensions'))
	->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_usersync'));
	$db->setQuery($dbQuery);
	$manifest = json_decode($db->loadResult());

	return($manifest->version);
	}

    /**
     * Formats timezone as: America/Chicago (UTC -06:00)
     * If $browserOffsetMinutes is provided (JS Date.getTimezoneOffset), it is used; otherwise server computes offset (DST-safe).
     */
    public static function moFormatTimezoneWithUtcOffset($tzName, $browserOffsetMinutes = null)
    {
        $tzName = trim((string) $tzName);
        if ($tzName === '') {
            $tzName = 'UTC';
        }

        if ($browserOffsetMinutes !== null && preg_match('/^-?\d+$/', (string) $browserOffsetMinutes)) {
            $m = (int) $browserOffsetMinutes; // minutes behind UTC
            $sign = $m > 0 ? '-' : '+';
            $abs = abs($m);
            $hh = str_pad((string) floor($abs / 60), 2, '0', STR_PAD_LEFT);
            $mm = str_pad((string) ($abs % 60), 2, '0', STR_PAD_LEFT);
            return $tzName . ' (UTC ' . $sign . $hh . ':' . $mm . ')';
        }

        try {
            $tzObj = new \DateTimeZone($tzName);
            $dt = new \DateTime('now', $tzObj);
            $offsetSeconds = (int) $dt->getOffset();
            $sign = $offsetSeconds >= 0 ? '+' : '-';
            $abs = abs($offsetSeconds);
            $hh = str_pad((string) floor($abs / 3600), 2, '0', STR_PAD_LEFT);
            $mm = str_pad((string) floor(($abs % 3600) / 60), 2, '0', STR_PAD_LEFT);
            return $tzName . ' (UTC ' . $sign . $hh . ':' . $mm . ')';
        } catch (\Exception $e) {
            return 'UTC (UTC +00:00)';
        }
    }


	public static function moGetJoomlaGroups(){
		
		$db = self::moGetDatabase();
		$db->setQuery($db->getQuery(true)
			->select('*')
			->from("#__usergroups")
			->where($db->quoteName('title').'!='.$db->quote('Super Users').'AND'.$db->quoteName('title').'!='.$db->quote('Public').'AND'.$db->quoteName('title').'!='.$db->quote('Guest'))
		);
		return $db->loadRowList();
	}

    public static function moSelectQuery($table, $columns = ['*'], $conditions = [])
    {
        $db = self::moGetDatabase();
        $query = $db->getQuery(true)
                    ->select(implode(',', $columns))
                    ->from($db->quoteName($table));

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $query->where($db->quoteName($column) . ' = ' . $db->quote($value));
            }
        }

        $db->setQuery($query);
        return $db->loadAssoc();
    }

	public static function moGetTableDetails($tableName,$condition=TRUE,$method='loadAssoc',$columns='*'){	

        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $columns = is_array($columns)?$db->quoteName($columns):$columns;
        $query->select($columns);
        $query->from($db->quoteName($tableName));
        if($condition!==TRUE)
        {
            foreach ($condition as $key=>$value)
                $query->where($db->quoteName($key) . " = " . $db->quote($value));
        }

        $db->setQuery($query);
        if ($method=='loadColumn')
            return $db->loadColumn();
        else if($method == 'loadObjectList')
            return $db->loadObjectList();
        else if($method == 'loadObject')
            return $db->loadObject();
        else if($method== 'loadResult')
            return $db->loadResult();
        else if($method == 'loadRow')
            return $db->loadRow();
        else if($method == 'loadRowList')
            return $db->loadRowList();
        else if($method == 'loadAssocList')
            return $db->loadAssocList();
        else
            return $db->loadAssoc();
    }

    public static function safeBase64Decode($string)
    {
        if (empty($string)) {
            return '';
        }

        // Detect if the string looks like Base64 (valid length and characters)
        if (preg_match('/^[A-Za-z0-9+\/=]+$/', $string) && strlen($string) % 4 === 0) {
            // Decoding trusted Base64 input (not obfuscation)
            $decoded = base64_decode($string, true);
            return $decoded !== false ? $decoded : '';
        }

        // Otherwise, treat it as URL-encoded text
        if (preg_match('/^[A-Za-z0-9%._@-]+$/', $string)) {
            return urldecode($string);
        }

        return '';
    }

    public function _load_db_values($table){
  
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $default_config = $db->loadAssoc();
        return $default_config;
    }

    public function loadDBValues($table, $load_by, $col_name = '*', $id_name = 'id', $id_value = 1){
        $db = self::moGetDatabase();
        $query = $db->getQuery(true);

        $query->select($col_name);

        $query->from($db->quoteName($table));
        if(is_numeric($id_value)){
            $query->where($db->quoteName($id_name)." = $id_value");

        }else{
            $query->where($db->quoteName($id_name) . " = " . $db->quote($id_value));
        }
        $db->setQuery($query);

        if($load_by == 'loadAssoc'){
            $default_config = $db->loadAssoc();
        }
        elseif ($load_by == 'loadResult'){
            $default_config = $db->loadResult();
        }
        elseif($load_by == 'loadColumn'){
            $default_config = $db->loadColumn();
        }
        return $default_config;
    }

    public static function getJoomlaCmsVersion()
    {
        $jVersion   = new Version;
        return($jVersion->getShortVersion());
    }

    public static function getServerType()
    {
        $server = $_SERVER['SERVER_SOFTWARE'] ?? '';

        if (stripos($server, 'Apache') !== false) {
            return 'Apache';
        }

        if (stripos($server, 'nginx') !== false) {
            return 'Nginx';
        }

        if (stripos($server, 'LiteSpeed') !== false) {
            return 'LiteSpeed';
        }

        if (stripos($server, 'IIS') !== false) {
            return 'IIS';
        }

        return 'Unknown';
    }

    public static function send_efficiency_mail($fromEmail, $content)
    {
        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $customer_details = (new MoUserSyncUtility)->_load_db_values('#__miniorange_usersync_customer');
        $customerKey = !empty($customer_details['customer_key']) ? $customer_details['customer_key'] : '16555';
        $apiKey = !empty($customer_details['api_key']) ? $customer_details['api_key'] : 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
        $currentTimeInMillis = round(microtime(true) * 1000);
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $headers = [
            "Content-Type: application/json",
            "Customer-Key: $customerKey",
            "Timestamp: $currentTimeInMillis",
            "Authorization: $hashValue"
        ];
        $fields = [
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => [
                'customerKey' => $customerKey,
                'fromEmail' => $fromEmail,
                'fromName' => 'miniOrange',
                'toEmail' => 'nutan.barad@xecurify.com',
                'bccEmail' => 'pritee.shinde@xecurify.com',
                'subject' => 'Installation of Joomla API Based User Provisioning [Free]',
                'content' => '<div>' . $content . '</div>',
            ],
        ];
        $field_string = json_encode($fields);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = 'SendMail CURL Error: ' . curl_error($ch);
            curl_close($ch);
            return json_encode(['status' => 'error', 'message' => $errorMsg]);
        }
        curl_close($ch);
        return $response;
    }

}
?>