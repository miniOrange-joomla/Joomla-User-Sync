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
 * Account Setup View
 *
 * @since  0.0.1
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
class miniorangeusersyncViewAccountSetup extends HtmlView
{
	function display($tpl = null)
	{
		// Get data from the model
		$this->lists		= $this->get('List');
		//$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(500, implode('<br />', $errors));

			return false;
		}
		$this->setLayout('accountsetup');
		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		
		ToolbarHelper::title(Text::_('COM_MINIORANGE_USERSYNC_PLUGIN_TITLE') , 'mo_logo mo_logo');
	}


}