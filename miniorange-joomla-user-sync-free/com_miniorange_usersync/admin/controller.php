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
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * General Controller of miniorange_usersync component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_usersync
 * @since       0.0.7
 */
use Joomla\CMS\MVC\Controller\BaseController;
class MiniorangeUserSyncController extends BaseController
{
	/**
	 * The default view for the display method.
	 *
	 * @var string
	 * @since 12.2
	 */
	protected $default_view = 'accountsetup';
}