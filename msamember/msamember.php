<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.utilities.date');

require_once($_SERVER['DOCUMENT_ROOT'] . "/swimman/includes/setup.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/swimman/includes/classes/Member.php");

/**
 * An example custom profile plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	User.profile
 * @version		1.6
 */
class plgUserMSAMember extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');
	}

	/**
	 * @param	string	$context	The context for the data
	 * @param	int		$data		The user id
	 * @param	object
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0)
			{
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles' .
					' WHERE user_id = '.(int) $userId." AND profile_key LIKE 'profile.%'" .
					' ORDER BY ordering'
				);
				$results = $db->loadRowList();

				// Check for a database error.
				if ($db->getErrorNum())
				{
					$this->_subject->setError($db->getErrorMsg());
					return false;
				}

				// Merge the profile data.
				$data->profile = array();

				foreach ($results as $v)
				{
					$k = str_replace('profile.', '', $v[0]);
					$data->profile[$k] = json_decode($v[1], true);

					if ($k == 'dob') {

					    if (strpos($data->profile[$k], "/") !== false ) {

					        $tempArr = explode('/', $data->profile[$k]);
                            $data->profile[$k] = $tempArr[2] . '-' . $tempArr[1] . '-' . $tempArr[0];

                        }

					    $tempDate = new JDate($data->profile[$k]);
                        $data->profile[$k] = $tempDate->format('d/m/Y');

                    }

					if ($data->profile[$k] === null)
					{
						$data->profile[$k] = $v[1];
					}
				}
			}

			if (!JHtml::isRegistered('users.url'))
			{
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}
			if (!JHtml::isRegistered('users.dob'))
			{
				JHtml::register('users.dob', array(__CLASS__, 'dob'));
			}
			if (!JHtml::isRegistered('users.tos'))
			{
				JHtml::register('users.tos', array(__CLASS__, 'tos'));
			}
		}

		return true;
	}

	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			$value = htmlspecialchars($value);
			if (substr ($value, 0, 4) == "http")
			{
				return '<a href="'.$value.'">'.$value.'</a>';
			}
			else
			{
				return '<a href="http://'.$value.'">'.$value.'</a>';
			}
		}
	}

	public static function dob($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
            if (strpos($value, "/") !== false ) {

                $tempArr = explode('/', $value);
                $$value = $tempArr[2] . '-' . $tempArr[1] . '-' . $tempArr[0];

            }

		    return $value;
		}
	}

	public static function tos($value)
	{
		if ($value)
		{
			return JText::_('JYES');
		}
		else
		{
			return JText::_('JNO');
		}
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__) . '/profiles');
		$form->loadFile('profile', false);

		$fields = array(
			'msanumber',
			'club',
			'dob',
			'tos',
		);

		$tosarticle = $this->params->get('register_tos_article');
		$tosenabled = $this->params->get('register-require_tos', 0);

		// We need to be in the registration form, field needs to be enabled and we need an article ID
		if ($name != 'com_users.registration' || !$tosenabled || !$tosarticle)
		{
			// We only want the TOS in the registration form
			$form->removeField('tos', 'profile');
		}
		else
		{
			// Push the TOS article ID into the TOS field.
			$form->setFieldAttribute('tos', 'article', $tosarticle, 'profile');
		}

		foreach ($fields as $field)
		{
			// Case using the users manager in admin
			if ($name == 'com_users.user')
			{
				// Remove the field if it is disabled in registration and profile
				if ($this->params->get('register-require_' . $field, 1) == 0
					&& $this->params->get('profile-require_' . $field, 1) == 0)
				{
					$form->removeField($field, 'profile');
				}
			}
			// Case registration
			elseif ($name == 'com_users.registration')
			{
				// Toggle whether the field is required.
				if ($this->params->get('register-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'profile');
				}
				else
				{
					$form->removeField($field, 'profile');
				}
			}
			// Case profile in site or admin
			elseif ($name == 'com_users.profile' || $name == 'com_admin.profile')
			{
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profile');
				}
				else
				{
					$form->removeField($field, 'profile');
				}
			}
		}

		return true;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['profile']) && (count($data['profile'])))
		{
			try
			{
				//Sanitize the date
				if (!empty($data['profile']['dob']))
				{

                    if (strpos($data['profile']['dob'], "/") !== false ) {

                        $tempArr = explode('/', $data['profile']['dob']);
                        $data['profile']['dob'] = $tempArr[2] . '-' . $tempArr[1] . '-' . $tempArr[0];

                    }

					$date = new JDate($data['profile']['dob']);
					$data['profile']['dob'] = $date->format('Y-m-d');
				}

				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'profile.%'"
				);

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}

				$tuples = array();
				$order	= 1;

				$jMSANumber = '';
				$jDob = '';
				$jClub = '';

				foreach ($data['profile'] as $k => $v)
				{
					$tuples[] = '('.$userId.', '.$db->quote('profile.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';

					// Populate values for check of link prospects
					switch ($k) {
						case 'dob':

                            if (strpos($v, "/") !== false ) {

                                $tempArr = explode('/', $v);
                                $v = $tempArr[2] . '-' . $tempArr[1] . '-' . $tempArr[0];

                            }

							$jDob = $v;
							break;
						case 'msanumber':
							$jMSANumber = $v;
							break;
						case 'club':
							$jClub = $v;
							break;
					}

				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}

				$db->setQuery('SELECT username FROM #__users WHERE id = ' . $db->quote($userId));
				$jusername = $db->loadResult();

 				// Create Joomla to swimman link
 				if ($jMSANumber != "") {

 					$member = new Member();
 					$member->loadNumber($jMSANumber);

 					if ($member->getDob() == $jDob) {
						
 						// We have a match, create link if it doesn't already exist
 						$member->linkJUser($userId, $jusername);
 						addlog("Joomla", "New Joomla Signup", "Signup with MSA Number: " . $jMSANumber . 
 								"and Username: " . $jusername);
						
 					}
					
 				}

			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}

			// Redirect to login
            // Copied from Webemus Autologin
            if ($isNew) {

                $app = JFactory::getApplication();

                // Set return URL
                $item = $app->getMenu()->getItem($this->params->get('login'));

                // Go to home page
                $return = JUri::base();

                if ($item) {
                    $lang = '';

                    if (JLanguageMultilang::isEnabled() && $item->language !== '*') {
                        $lang = '&lang=' . $item->id . $lang;
                    }

                    $return = 'index.php?Itemid=' . $item->id . $lang;
                }

                // Set return URL
                $app->setUserState('users.login.form.return', $return);

                // Get log in options
                $options = array();
                $options['remember'] = false;
                $options['return'] = JURI::base();

                // Get the login credentials
                $credentials = array();
                $credentials['username'] = $data['username'];
                $credentials['password'] = $data['password1'];

                // Perform the log in
                $app->login($credentials, $options);
                $app->setUserState('users.login.form.data', array());
                $app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));

            }

            // Refresh the permissions
            $user = JFactory::getUser();
            $session = JFactory::getSession();
            $session->set('user', new JUser($user->id));

            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_('Tried to refresh permissions ' . $user->id), 'error');
            $menu = $app->getMenu();
            $menu->load();

		}

		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
					" AND profile_key LIKE 'profile.%'"
				);

				if (!$db->query())
				{
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
