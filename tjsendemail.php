<?php
/**
 * @package     Tjreports.Plugins
 * @subpackage  Plugins,system,plg_system_sendemail
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

JHtml::_('jquery.token');

$lang = JFactory::getLanguage();
$lang->load('plg_system_tjsendemail', JPATH_ADMINISTRATOR);

/**
 * Plugin to send email in a bulk.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemTjSendemail extends JPlugin
{
	/**
	 * Constructor - Function used as a contructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @retunr  class object
	 *
	 * @since  __DEPLOY_VERSION__
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

		$style = '.is-progress {
		background-color: #EEF2F6;
		cursor: not-allowed;
		z-index: 5;
		opacity: 0.6;
		-webkit-transition: background-color 500ms ease-out 1s;
		-moz-transition: background-color 500ms ease-out 1s;
		-o-transition: background-color 500ms ease-out 1s;
		transition: background-color 500ms ease-out 1s;
		position: relative;

		}

		.is-progress:before {
		font-family: "FontAwesome";
		content: "\f110";
		position: absolute;
		z-index:1040;
		left: 50%;
		top: 50%;
		font-size: 45px;
		color: #1664bd;
		-webkit-animation: fa-spin 2s infinite linear;
		animation: fa-spin 2s infinite linear;
		transform: translate(-50%, -50%);
		text-align: center;
		}';
		$document->addStyleDeclaration($style);

		parent::__construct($subject, $config);
	}

	/**
	 * Ajax call funcation to send email
	 *
	 * @return  none
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onAjaxtjsendemail()
	{
		Session::checkToken('post') or new JResponseJson(null, Text::_('JINVALID_TOKEN_NOTICE'), true);

		// Add logs
		JLog::addLogger(
			array(
				'text_file' => 'sendEmail.logs.php'
			)
		);

		if (!Factory::getUser()->id)
		{
			echo new JResponseJson(null, Text::_('JERROR_ALERTNOAUTHOR'), true);
			jexit();
		}

		$app = Factory::getApplication();
		$config = Factory::getConfig();
		$ccMail = $config->get('mailfrom');

		if (!$ccMail)
		{
			echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_ERROR_NO_FROMEMAIL'), true);

			jexit();
		}

		$templateData = $app->input->post->get('template', '', 'array');
		$emails = $app->input->post->get('emails', '', 'array');

		if (empty($emails))
		{
			echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_ADD_RECIPIENTS_OR_CHECK_PREFERENCES'), true);

			jexit();
		}

		try
		{
			// Remove duplicate emails
			$emails = array_unique($emails);

			// The mail subject.
			$emailSubject = $templateData['subject'];

			// The mail body.
			$emailBody = $templateData['message'];

			$successCnt = $failCnt = 0;
			$errorFlag = False;

			foreach ($emails as $singleEmail)
			{
				// Send Email
				$return = Factory::getMailer()->sendMail($config->get('mailfrom'), $config->get('fromname'), trim($singleEmail), $emailSubject, $emailBody, true);

				if ($return !== true)
				{
					$failCnt++;
					JLog::add(Text::sprintf('PLG_SYSTEM_SENDEMAIL_FAIL_TO_SENDEMAIL', $singleEmail, $return->get("message")));
				}
				else
				{
					$successCnt++;
					JLog::add(Text::sprintf('PLG_SYSTEM_SENDEMAIL_SUCCESSFULLY_SENDEMAIL', $singleEmail));
				}
			}

			if ($failCnt == 0)
			{
				$msg = Text::_('PLG_SYSTEM_SENDEMAIL_SUCCESSFULLY_SEND_ALL');
			}
			else if ($successCnt == 0)
			{
				$msg = Text::_('PLG_SYSTEM_SENDEMAIL_FAIL_TO_SENDEMAIL_ALL');
				$errorFlag =  true;
			}
			else
			{
				$msg = Text::_('PLG_SYSTEM_SENDEMAIL_OUTPUT', $successCnt, $failCnt);
			}

			echo new JResponseJson(null, $msg, $errorFlag);

			jexit();
		}
		catch (Exception $e)
		{
			echo new JResponseJson(null, Text::_('PLG_SYSTEM_SENDEMAIL_ERROR'), true);

			jexit();
		}
	}
}
