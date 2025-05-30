<?php

return [
  'name' => 'Activity',
  'table' => 'civicrm_activity',
  'class' => 'CRM_Activity_DAO_Activity',
  'getInfo' => fn() => [
    'title' => ts('Activity'),
    'title_plural' => ts('Activities'),
    'description' => ts('Past or future actions concerning one or more contacts.'),
    'log' => TRUE,
    'add' => '1.1',
    'icon' => 'fa-tasks',
    'label_field' => 'subject',
  ],
  'getPaths' => fn() => [
    'add' => 'civicrm/activity?reset=1&action=add&context=standalone',
    'view' => 'civicrm/activity?reset=1&action=view&id=[id]',
    'update' => 'civicrm/activity/add?reset=1&action=update&id=[id]',
    'delete' => 'civicrm/activity?reset=1&action=delete&id=[id]',
  ],
  'getIndices' => fn() => [
    'UI_source_record_id' => [
      'fields' => [
        'source_record_id' => TRUE,
      ],
      'add' => '3.2',
    ],
    'UI_activity_type_id' => [
      'fields' => [
        'activity_type_id' => TRUE,
      ],
      'add' => '1.6',
    ],
    'index_activity_date_time' => [
      'fields' => [
        'activity_date_time' => TRUE,
      ],
      'add' => '4.7',
    ],
    'index_status_id' => [
      'fields' => [
        'status_id' => TRUE,
      ],
      'add' => '4.7',
    ],
    'index_is_current_revision' => [
      'fields' => [
        'is_current_revision' => TRUE,
      ],
      'add' => '2.2',
    ],
    'index_is_deleted' => [
      'fields' => [
        'is_deleted' => TRUE,
      ],
      'add' => '2.2',
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => ts('Activity ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => ts('Unique Other Activity ID'),
      'add' => '1.1',
      'unique_name' => 'activity_id',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'source_record_id' => [
      'title' => ts('Source Record'),
      'sql_type' => 'int unsigned',
      'input_type' => NULL,
      'readonly' => TRUE,
      'description' => ts('Artificial FK to original transaction (e.g. contribution) IF it is not an Activity. Entity table is discovered by filtering by the appropriate activity_type_id.'),
      'add' => '2.0',
    ],
    'activity_type_id' => [
      'title' => ts('Activity Type ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'required' => TRUE,
      'description' => ts('FK to civicrm_option_value.id, that has to be valid, registered activity type.'),
      'add' => '1.1',
      'default' => 1,
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'input_attrs' => [
        'label' => ts('Activity Type'),
      ],
      'pseudoconstant' => [
        'option_group_name' => 'activity_type',
      ],
    ],
    'subject' => [
      'title' => ts('Subject'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('The subject/purpose/short description of the activity.'),
      'add' => '1.1',
      'unique_name' => 'activity_subject',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'activity_date_time' => [
      'title' => ts('Activity Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => ts('Date and time this activity is scheduled to occur. Formerly named scheduled_date_time.'),
      'add' => '2.0',
      'default' => 'CURRENT_TIMESTAMP',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'duration' => [
      'title' => ts('Duration'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => ts('Planned or actual duration of activity expressed in minutes. Conglomerate of former duration_hours and duration_minutes.'),
      'add' => '2.0',
      'unique_name' => 'activity_duration',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'location' => [
      'title' => ts('Location'),
      'sql_type' => 'varchar(2048)',
      'input_type' => 'Text',
      'description' => ts('Location of the activity (optional, open text).'),
      'add' => '1.1',
      'unique_name' => 'activity_location',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'phone_id' => [
      'title' => ts('Phone ID (called)'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'deprecated' => TRUE,
      'description' => ts('Phone ID of the number called (optional - used if an existing phone number is selected).'),
      'add' => '2.0',
      'input_attrs' => [
        'label' => ts('Phone (called)'),
      ],
      'entity_reference' => [
        'entity' => 'Phone',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'phone_number' => [
      'title' => ts('Phone (called) Number'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'deprecated' => TRUE,
      'description' => ts('Phone number in case the number does not exist in the civicrm_phone table.'),
      'add' => '2.0',
    ],
    'details' => [
      'title' => ts('Details'),
      'sql_type' => 'longtext',
      'input_type' => 'RichTextEditor',
      'description' => ts('Details about the activity (agenda, notes, etc).'),
      'add' => '1.1',
      'unique_name' => 'activity_details',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'input_attrs' => [
        'rows' => 8,
        'cols' => 60,
      ],
    ],
    'status_id' => [
      'title' => ts('Activity Status'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => ts('ID of the status this activity is currently in. Foreign key to civicrm_option_value.'),
      'add' => '2.0',
      'unique_name' => 'activity_status_id',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'pseudoconstant' => [
        'option_group_name' => 'activity_status',
      ],
    ],
    'priority_id' => [
      'title' => ts('Priority'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => ts('ID of the priority given to this activity. Foreign key to civicrm_option_value.'),
      'add' => '2.0',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'pseudoconstant' => [
        'option_group_name' => 'priority',
      ],
    ],
    'parent_id' => [
      'title' => ts('Parent Activity ID'),
      'sql_type' => 'int unsigned',
      'input_type' => NULL,
      'readonly' => TRUE,
      'description' => ts('Parent meeting ID (if this is a follow-up item).'),
      'add' => '1.1',
      'input_attrs' => [
        'label' => ts('Parent Activity'),
      ],
      'entity_reference' => [
        'entity' => 'Activity',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'is_test' => [
      'title' => ts('Test'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'add' => '2.0',
      'unique_name' => 'activity_is_test',
      'default' => FALSE,
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'medium_id' => [
      'title' => ts('Activity Medium'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => ts('Activity Medium, Implicit FK to civicrm_option_value where option_group = encounter_medium.'),
      'add' => '2.2',
      'unique_name' => 'activity_medium_id',
      'default' => NULL,
      'pseudoconstant' => [
        'option_group_name' => 'encounter_medium',
      ],
    ],
    'is_auto' => [
      'title' => ts('Auto'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'add' => '2.2',
      'default' => FALSE,
    ],
    'relationship_id' => [
      'title' => ts('Relationship ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'deprecated' => TRUE,
      'description' => ts('FK to Relationship ID'),
      'add' => '2.2',
      'default' => NULL,
      'input_attrs' => [
        'label' => ts('Relationship'),
      ],
      'entity_reference' => [
        'entity' => 'Relationship',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'is_current_revision' => [
      'title' => ts('Is current (unused)'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'deprecated' => TRUE,
      'description' => ts('Unused deprecated column.'),
      'add' => '2.2',
      'default' => TRUE,
    ],
    'original_id' => [
      'title' => ts('Original ID (unused)'),
      'sql_type' => 'int unsigned',
      'input_type' => NULL,
      'deprecated' => TRUE,
      'readonly' => TRUE,
      'description' => ts('Unused deprecated column.'),
      'add' => '2.2',
      'input_attrs' => [
        'label' => ts('Original Activity'),
      ],
      'entity_reference' => [
        'entity' => 'Activity',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'result' => [
      'title' => ts('Result'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Currently being used to store result id for survey activity, FK to option value.'),
      'add' => '3.3',
      'unique_name' => 'activity_result',
    ],
    'is_deleted' => [
      'title' => ts('Activity is in the Trash'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'add' => '2.2',
      'unique_name' => 'activity_is_deleted',
      'default' => FALSE,
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'campaign_id' => [
      'title' => ts('Campaign ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => ts('The campaign for which this activity has been triggered.'),
      'add' => '3.4',
      'unique_name' => 'activity_campaign_id',
      'component' => 'CiviCampaign',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'input_attrs' => [
        'label' => ts('Campaign'),
      ],
      'pseudoconstant' => [
        'table' => 'civicrm_campaign',
        'key_column' => 'id',
        'label_column' => 'title',
        'prefetch' => 'disabled',
      ],
      'entity_reference' => [
        'entity' => 'Campaign',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'engagement_level' => [
      'title' => ts('Engagement Index'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => ts('Assign a specific level of engagement to this activity. Used for tracking constituents in ladder of engagement.'),
      'add' => '3.4',
      'unique_name' => 'activity_engagement_level',
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
      'pseudoconstant' => [
        'option_group_name' => 'engagement_index',
      ],
    ],
    'weight' => [
      'title' => ts('Order'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'add' => '4.1',
    ],
    'is_star' => [
      'title' => ts('Is Starred'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => ts('Activity marked as favorite.'),
      'add' => '4.7',
      'default' => FALSE,
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'created_date' => [
      'title' => ts('Created Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => ts('When was the activity was created.'),
      'add' => '4.7',
      'unique_name' => 'activity_created_date',
      'default' => 'CURRENT_TIMESTAMP',
      'usage' => [
        'export',
      ],
      'input_attrs' => [
        'label' => ts('Created Date'),
      ],
    ],
    'modified_date' => [
      'title' => ts('Modified Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'readonly' => TRUE,
      'description' => ts('When was the activity (or closely related entity) was created or modified or deleted.'),
      'add' => '4.7',
      'unique_name' => 'activity_modified_date',
      'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
      'usage' => [
        'export',
      ],
      'input_attrs' => [
        'label' => ts('Modified Date'),
      ],
    ],
  ],
];
