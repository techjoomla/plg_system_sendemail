<?php
/**
 * @version    SVN: <svn_id>
 * @package    Plg_System_Tjlms
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');
jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

JHtml::_('jquery.token');

$lang = JFactory::getLanguage();
$lang->load('plg_system_sendemail', JPATH_ADMINISTRATOR);

/**
 * Methods supporting a list of Tjlms action.
 *
 * @since  1.0.0
 */
class PlgSystemplg_System_Sendemail extends JPlugin
{
	/**
	 * Constructor - Function used as a contructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @retunr  class object
	 *
	 * @since  1.0.0
	 */
	public function __construct($subject, $config)
	{
		Text::script('PLG_SYSTEM_SENDEMAIL_BTN');
		Text::script('PLG_SYSTEM_SENDEMAIL_SELECT_CHECKBOX');
		Text::script('PLG_SYSTEM_SENDEMAIL_POPUP_HEADING');
		Text::script('PLG_SYSTEM_SENDEMAIL_POPUP_EMAIL_SUBJECT');
		Text::script('PLG_SYSTEM_SENDEMAIL_POPUP_EMAIL_BODY_MESSAGE');
		Text::script('PLG_SYSTEM_SENDEMAIL_POPUP_SEND_BTN');

		$document = Factory::getDocument();
		$document->addScript(JUri::root(true) . '/plugins/system/plg_system_sendemail/bulksendemail.js');
		$document->addScriptDeclaration("
			jQuery(document).ready(function() {
				tjutilitysendemail.initialize('report-table');
			});"
		);
		parent::__construct($subject, $config);
	}

	/**
	 * Ajax call funcation to send email
	 *
	 * @return  none
	 *
	 * @since  1.0.0
	 */
	public function onAjaxplg_System_Sendemail()
	{
		Session::checkToken('post') or die(Text::_('JINVALID_TOKEN_NOTICE'));

		if (!Factory::getUser()->id)
		{
			echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true, true);
			jexit();
		}

		$app = Factory::getApplication();
		$config = Factory::getConfig();
		$ccMail = $config->get('mailfrom');

		if (!$ccMail)
		{
			echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_ERROR_NO_FROMEMAIL'), true, true);

			jexit();
		}

		$templateData = $app->input->post->get('template', '', 'array');
		$emails = $app->input->post->get('emails', '', 'array');

		if (!empty($emails))
		{
			try
			{
				// Remove duplicate emails
				$emails = array_unique($emails);

				// The mail subject.
				$emailSubject = $templateData['subject'];

				// The mail body.
				$emailBody = $templateData['message'];

				foreach ($emails as $singleEmail)
				{
					// Send Email
					Factory::getMailer()->sendMail(
						$config->get('mailfrom'),
						$config->get('fromname'),
						trim($singleEmail),
						$emailSubject,
						$emailBody
					);
				}

				echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_SUCCESSFULLY_SEND'), false, true);

				jexit();
			}
			catch (Exception $e)
			{
				echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_ERROR'), true, true);

				jexit();
			}
		}
		else
		{
			echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_ADD_RECIPIENTS_OR_CHECK_PREFERENCES'), true, true);

			jexit();
		}
	}
}
