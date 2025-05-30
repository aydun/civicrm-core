<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */
class CRM_Contact_Page_View_GroupContact extends CRM_Core_Page {

  /**
   * The contact being viewed
   *
   * @var int
   * @internal
   */
  public $_contactId;

  /**
   * Called when action is browse.
   */
  public function browse() {

    $count = CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, NULL, NULL, TRUE, FALSE, FALSE, TRUE, NULL, TRUE);

    $in = CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, 'Added', NULL, FALSE, FALSE, FALSE, TRUE, NULL, TRUE);
    $pending = CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, 'Pending', NULL, FALSE, FALSE, FALSE, TRUE, NULL, TRUE);
    $out = CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, 'Removed', NULL, FALSE, FALSE, FALSE, TRUE, NULL, TRUE);

    // keep track of all 'added' contact groups so we can remove them from the smart group
    // section
    $staticGroups = [];
    if (!empty($in)) {
      foreach ($in as $group) {
        $staticGroups[$group['group_id']] = 1;
      }
    }

    $this->assign('groupCount', $count);
    $this->assign('groupIn', $in);
    $this->assign('groupPending', $pending);
    $this->assign('groupOut', $out);

    // get the info on contact smart groups
    $contactSmartGroupSettings = Civi::settings()->get('contact_smart_group_display');
    $this->assign('contactSmartGroupSettings', $contactSmartGroupSettings);

    $this->ajaxResponse['tabCount'] = count($in);
  }

  /**
   * called when action is update.
   *
   * @param int $groupId
   *
   */
  public function edit($groupId = NULL) {
    $controller = new CRM_Core_Controller_Simple(
      'CRM_Contact_Form_GroupContact',
      ts('Contact\'s Groups'),
      $this->_action
    );
    $controller->setEmbedded(TRUE);

    // set the userContext stack
    $session = CRM_Core_Session::singleton();

    $session->pushUserContext(
      CRM_Utils_System::url(
        'civicrm/contact/view',
        "action=browse&selectedChild=group&cid={$this->_contactId}"
      ),
      FALSE
    );
    $controller->reset();

    $controller->set('contactId', $this->_contactId);
    $controller->set('groupId', $groupId);

    $controller->process();
    $controller->run();
  }

  public function preProcess() {
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
    $this->assign('contactId', $this->_contactId);

    // check logged in url permission
    CRM_Contact_Page_View::checkUserPermission($this);

    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    $this->assign('action', $this->_action);
  }

  /**
   * the main function that is called
   * when the page loads, it decides the which action has
   * to be taken for the page.
   *
   * @return null
   */
  public function run() {
    $this->preProcess();

    $displayName = CRM_Contact_BAO_Contact::displayName($this->_contactId);
    $this->assign('displayName', $displayName);

    if ($this->_action == CRM_Core_Action::DELETE) {
      $groupContactId = CRM_Utils_Request::retrieve('gcid', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
      $status = CRM_Utils_Request::retrieve('st', 'String', CRM_Core_DAO::$_nullObject, TRUE);
      if (is_numeric($groupContactId) && $status) {
        $this->del($groupContactId, $status, $this->_contactId);
      }
      $session = CRM_Core_Session::singleton();
      CRM_Utils_System::redirect($session->popUserContext());
    }

    $this->edit(NULL, CRM_Core_Action::ADD);
    $this->browse();
    return parent::run();
  }

  /**
   * Remove/ rejoin the group
   *
   * @param int $groupContactId
   *   Id of crm_group_contact.
   * @param string $status
   *   This is the status that should be updated.
   *
   * $access public
   * @param int $contactID
   *
   * @return bool
   */
  public static function del($groupContactId, $status, $contactID) {
    $groupId = CRM_Contact_BAO_GroupContact::getGroupId($groupContactId);

    switch ($status) {
      case 'i':
        $groupStatus = 'Added';
        break;

      case 'p':
        $groupStatus = 'Pending';
        break;

      case 'o':
        $groupStatus = 'Removed';
        break;

      case 'd':
        $groupStatus = 'Deleted';
        break;
    }

    $groupNum = CRM_Contact_BAO_GroupContact::getContactGroup($contactID, 'Added', NULL, TRUE, TRUE);
    if ($groupNum == 1 && $groupStatus == 'Removed' && Civi::settings()->get('multisite_is_enabled')) {
      CRM_Core_Session::setStatus(ts('Please ensure at least one contact group association is maintained.'), ts('Could Not Remove'));
      return FALSE;
    }

    $ids = [$contactID];
    $method = 'Admin';

    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');

    if ($userID == $contactID) {
      $method = 'Web';
    }

    CRM_Contact_BAO_GroupContact::removeContactsFromGroup($ids, $groupId, $method, $groupStatus);
  }

}
