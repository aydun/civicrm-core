<?php

return [
  'name' => 'PaymentProcessorType',
  'table' => 'civicrm_payment_processor_type',
  'class' => 'CRM_Financial_DAO_PaymentProcessorType',
  'getInfo' => fn() => [
    'title' => ts('Payment Processor Type'),
    'title_plural' => ts('Payment Processor Types'),
    'description' => ts('FIXME'),
    'add' => '1.8',
  ],
  'getPaths' => fn() => [
    'add' => 'civicrm/admin/paymentProcessorType?reset=1&action=add',
    'delete' => 'civicrm/admin/paymentProcessorType?reset=1&action=delete&id=[id]',
    'update' => 'civicrm/admin/paymentProcessorType?reset=1&action=update&id=[id]',
  ],
  'getIndices' => fn() => [
    'UI_name' => [
      'fields' => [
        'name' => TRUE,
      ],
      'unique' => TRUE,
      'add' => '2.1',
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => ts('Payment Processor Type ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => ts('Payment Processor Type ID'),
      'add' => '1.8',
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'name' => [
      'title' => ts('Payment Processor Type variable name to be used in code'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => ts('Payment Processor Type Name.'),
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 64,
      ],
    ],
    'title' => [
      'title' => ts('Payment Processor Type Title'),
      'sql_type' => 'varchar(127)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => ts('Payment Processor Type Title.'),
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 127,
      ],
    ],
    'description' => [
      'title' => ts('Processor Type Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => ts('Payment Processor Description.'),
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'is_active' => [
      'title' => ts('Processor Type Is Active?'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => ts('Is this processor active?'),
      'add' => '1.8',
      'default' => TRUE,
      'input_attrs' => [
        'label' => ts('Enabled'),
      ],
    ],
    'is_default' => [
      'title' => ts('Processor Type is Default?'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => ts('Is this processor the default?'),
      'add' => '1.8',
      'default' => FALSE,
      'input_attrs' => [
        'label' => ts('Default'),
      ],
    ],
    'user_name_label' => [
      'title' => ts('Label for User Name if used'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'password_label' => [
      'title' => ts('Label for password'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'signature_label' => [
      'title' => ts('Label for Signature'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'subject_label' => [
      'title' => ts('Label for Subject'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'class_name' => [
      'title' => ts('Suffix for PHP class name implementation'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_site_default' => [
      'title' => ts('Default Live Site URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_api_default' => [
      'title' => ts('Default API Site URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_recur_default' => [
      'title' => ts('Default Live Recurring Payments URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_button_default' => [
      'title' => ts('Default Live Button URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_site_test_default' => [
      'title' => ts('Default Test Site URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_api_test_default' => [
      'title' => ts('Default Test API URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_recur_test_default' => [
      'title' => ts('Default Test Recurring Payment URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'url_button_test_default' => [
      'title' => ts('Default Test Button URL'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'add' => '1.8',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ],
    'billing_mode' => [
      'title' => ts('Billing Mode'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'required' => TRUE,
      'description' => ts('Billing Mode (deprecated)'),
      'add' => '1.8',
      'input_attrs' => [
        'label' => ts('Billing Mode'),
      ],
      'pseudoconstant' => [
        'callback' => 'CRM_Core_SelectValues::billingMode',
      ],
    ],
    'is_recur' => [
      'title' => ts('Processor Type Supports Recurring?'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => ts('Can process recurring contributions'),
      'add' => '1.8',
      'default' => FALSE,
    ],
    'payment_type' => [
      'title' => ts('Processor Type Payment Type'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => ts('Payment Type: Credit or Debit (deprecated)'),
      'add' => '3.0',
      'default' => 1,
    ],
    'payment_instrument_id' => [
      'title' => ts('Payment Method'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => ts('Payment Instrument ID'),
      'add' => '4.7',
      'default' => 1,
      'pseudoconstant' => [
        'option_group_name' => 'payment_instrument',
      ],
    ],
  ],
];