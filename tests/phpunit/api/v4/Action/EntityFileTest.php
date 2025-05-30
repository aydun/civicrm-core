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


namespace api\v4\Action;

use api\v4\Api4TestBase;
use Civi\Api4\Activity;
use Civi\Api4\EntityFile;
use Civi\Api4\EntityTag;
use Civi\Api4\File;
use Civi\Api4\Note;
use Civi\Core\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
class EntityFileTest extends Api4TestBase implements TransactionalInterface, HookInterface {

  use \Civi\Test\ACLPermissionTrait;

  public function testContactAclForNoteAttachment() {
    $cid = $this->saveTestRecords('Contact', ['records' => 4])
      ->column('id');
    $note = $this->saveTestRecords('Note', [
      'records' => [
        ['entity_id' => $cid[0], 'note' => '0test'],
        ['entity_id' => $cid[1], 'note' => '1test'],
        ['entity_id' => $cid[2], 'note' => '2test'],
        ['entity_id' => $cid[3], 'note' => '3test'],
      ],
      'defaults' => ['entity_table' => 'civicrm_contact'],
    ])->column('id');

    $file = [];

    foreach ($note as $nid) {
      $fileId = $this->createTestRecord('File', [
        'file_name' => 'file_for_' . $nid . '.txt',
        'mime_type' => 'text/plain',
        'content' => 'hello',
      ])['id'];
      $file[] = $fileId;
      $this->createTestRecord('EntityFile', [
        'file_id' => $fileId,
        'entity_table' => 'civicrm_note',
        'entity_id' => $nid,
      ]);
    }

    // Grant access to contact 2 & 3, deny to 0 & 1
    $this->allowedContacts = array_slice($cid, 2);
    \CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access uploaded files', 'view debug output'];
    \CRM_Utils_Hook::singleton()->setHook('civicrm_aclWhereClause', [$this, 'aclWhereMultipleContacts']);

    $allowedEntityFiles = EntityFile::get()
      ->addWhere('entity_table', '=', 'civicrm_note')
      ->addWhere('entity_id', 'IN', $note)
      ->setDebug(TRUE)
      ->execute();
    // ACL clause should have been inserted
    $this->assertStringContainsString('civicrm_acl_contact_cache', $allowedEntityFiles->debug['sql'][0]);
    // Results should have been filtered by allowed contacts
    $this->assertCount(2, $allowedEntityFiles);

    // Disabling - see comment in CRM_Core_BAO_File::addSelectWhereClause()
    //  $allowedFiles = File::get()
    //    ->addWhere('id', 'IN', $file)
    //    ->setDebug(TRUE)
    //    ->execute();
    //  // ACL clause should have been inserted
    //  $this->assertStringContainsString('civicrm_acl_contact_cache', $allowedFiles->debug['sql'][0]);
    //  // Results should have been filtered by allowed contacts
    //  $this->assertCount(2, $allowedFiles);

    $allowedNotes = Note::get()
      ->addJoin('File AS file', 'LEFT', 'EntityFile', ['file.entity_id', '=', 'id'], ['file.entity_table', '=', '"civicrm_note"'])
      ->addSelect('file.file_name', 'file.url', 'note', 'id')
      ->setDebug(TRUE)
      ->execute()->indexBy('id');
    // ACL clause should have been inserted
    $this->assertStringContainsString('civicrm_acl_contact_cache', $allowedNotes->debug['sql'][0]);
    // Results should have been filtered by allowed contacts
    $this->assertCount(2, $allowedNotes);
    $this->assertEquals('file_for_' . $note[2] . '.txt', $allowedNotes[$note[2]]['file.file_name']);
    $this->assertEquals('file_for_' . $note[3] . '.txt', $allowedNotes[$note[3]]['file.file_name']);
    $this->assertStringContainsString("id=$file[2]&fcs=", $allowedNotes[$note[2]]['file.url']);
    $this->assertStringContainsString("id=$file[3]&fcs=", $allowedNotes[$note[3]]['file.url']);
  }

  public function testGetAggregateFileFields() {
    $activity = $this->createTestRecord('Activity');

    foreach (['text/plain' => 'txt', 'image/png' => 'png', 'image/jpg' => 'jpg'] as $mimeType => $ext) {
      $file = $this->createTestRecord('File', [
        'file_name' => 'test_file.' . $ext,
        'mime_type' => $mimeType,
        'content' => 'hello',
      ]);
      $this->createTestRecord('EntityFile', [
        'file_id' => $file['id'],
        'entity_table' => 'civicrm_activity',
        'entity_id' => $activity['id'],
      ]);
    }

    $get = Activity::get(FALSE)
      ->addWhere('id', '=', $activity['id'])
      ->addJoin('File AS file', 'LEFT', 'EntityFile', ['file.entity_id', '=', 'id'], ['file.entity_table', '=', '"civicrm_activity"'])
      ->addGroupBy('id')
      ->addSelect('GROUP_CONCAT(UNIQUE file.file_name) AS aggregate_file_name')
      ->addSelect('GROUP_CONCAT(UNIQUE file.url) AS aggregate_url')
      ->addSelect('GROUP_CONCAT(UNIQUE file.icon) AS aggregate_icon')
      ->execute()->single();

    $this->assertCount(3, $get['aggregate_url']);
    $this->assertCount(3, $get['aggregate_icon']);
    $this->assertEquals(['test_file.txt', 'test_file.png', 'test_file.jpg'], $get['aggregate_file_name']);
    $this->assertEquals(['fa-file-text-o', 'fa-file-image-o', 'fa-file-image-o'], $get['aggregate_icon']);
  }

  public function testDeleteTaggedAttachments(): void {
    $activity = $this->createTestRecord('Activity');

    $fileTag = $this->createTestRecord('Tag', [
      'used_for' => 'civicrm_file',
      'title' => uniqid(),
    ]);
    $activityTag = $this->createTestRecord('Tag', [
      'used_for' => 'civicrm_activity',
      'title' => uniqid(),
    ]);

    $activityEntityTag = $this->createTestRecord('EntityTag', [
      'entity_id' => $activity['id'],
      'entity_table' => 'civicrm_activity',
      'tag_id' => $activityTag['id'],
    ]);

    $file = $this->createTestRecord('File', [
      'file_name' => 'test_file.txt',
      'mime_type' => 'text/plain',
      'content' => 'hello',
    ]);

    $entityFile = $this->createTestRecord('EntityFile', [
      'file_id' => $file['id'],
      'entity_table' => 'civicrm_activity',
      'entity_id' => $activity['id'],
    ]);

    $fileEntityTag = $this->createTestRecord('EntityTag', [
      'entity_id' => $file['id'],
      'entity_table' => 'civicrm_file',
      'tag_id' => $fileTag['id'],
    ]);

    EntityFile::delete(FALSE)
      ->addWhere('id', '=', $entityFile['id'])
      ->execute();

    File::delete(FALSE)
      ->addWhere('id', '=', $file['id'])
      ->execute();

    Activity::delete(FALSE)
      ->addWhere('id', '=', $activity['id'])
      ->execute();

    $this->assertCount(0, EntityTag::get(FALSE)
      ->addWhere('id', 'IN', [$activityEntityTag['id'], $fileEntityTag['id']])
      ->execute());
  }

}
