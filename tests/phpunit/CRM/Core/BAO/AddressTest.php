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
 * Class CRM_Core_BAO_AddressTest
 * @group headless
 * @group locale
 */
class CRM_Core_BAO_AddressTest extends CiviUnitTestCase {

  use CRMTraits_Custom_CustomDataTrait;

  public function setUp(): void {
    parent::setUp();

    $this->quickCleanup(['civicrm_contact', 'civicrm_address']);
  }

  public function tearDown(): void {
    \Civi::settings()->set('pinnedContactCountries', []);
    CRM_Core_I18n::singleton()->setLocale('en_US');
    parent::tearDown();
  }

  /**
   * Create() method (create and update modes)
   */
  public function testCreate(): void {
    $contactId = $this->individualCreate();

    $params = [];
    $params['address']['1'] = [
      'street_address' => 'Oberoi Garden',
      'supplemental_address_1' => 'Attn: Accounting',
      'supplemental_address_2' => 'Powai',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
    ];

    $params['contact_id'] = $contactId;

    $fixAddress = TRUE;

    CRM_Core_BAO_Address::legacyCreate($params, $fixAddress);
    $addressId = $this->assertDBNotNull('CRM_Core_DAO_Address', 'Oberoi Garden', 'id', 'street_address',
      'Database check for created address.'
    );

    // Now call add() to modify an existing  address

    $params = [];
    $params['address']['1'] = [
      'id' => $addressId,
      'street_address' => '120 Terminal Road',
      'supplemental_address_1' => 'A-wing:3037',
      'supplemental_address_2' => 'Bandra',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
    ];
    $params['contact_id'] = $contactId;

    $block = CRM_Core_BAO_Address::legacyCreate($params, $fixAddress);

    $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for updated address by contactId.'
    );
    $this->assertDBNotNull('CRM_Core_DAO_Address', '120 Terminal Road', 'id', 'street_address',
      'Database check for updated address by street_name.'
    );
    $this->contactDelete($contactId);
  }

  /**
   * Add() method ( )
   */
  public function testAdd(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    $addAddress = CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $this->assertEquals($addAddress->street_address, 'E 906N Pine Pl W', 'In line' . __LINE__);
    $this->assertEquals($addAddress->supplemental_address_1, 'Editorial Dept', 'In line' . __LINE__);
    $this->assertEquals($addAddress->city, 'El Paso', 'In line' . __LINE__);
    $this->assertEquals($addAddress->postal_code, '88575', 'In line' . __LINE__);
    $this->assertEquals($addAddress->geo_code_1, '31.694842', 'In line' . __LINE__);
    $this->assertEquals($addAddress->geo_code_2, '-106.29998', 'In line' . __LINE__);
    $this->assertEquals($addAddress->country_id, '1228', 'In line' . __LINE__);
    $this->contactDelete($contactId);
  }

  /**
   * Add 2 billing addresses using the `CRM_Core_BAO_Address::legacyCreate` mode
   * Only the first array will remain as primary/billing due to the nature of how `legacyCreate` works
   */
  public function testMultipleBillingAddressesLegacymode(): void {
    $contactId = $this->individualCreate();

    $entityBlock = ['contact_id' => $contactId];
    $params = [];
    $params['contact_id'] = $contactId;
    $params['address']['1'] = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];
    $params['address']['2'] = [
      'street_address' => '120 Terminal Road',
      'supplemental_address_1' => 'A-wing:3037',
      'supplemental_address_2' => 'Bandra',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::legacyCreate($params, $fixAddress = TRUE);

    $address = CRM_Core_BAO_Address::getValues($entityBlock);

    $this->assertEquals($address[1]['contact_id'], $contactId);
    $this->assertEquals($address[1]['is_primary'], 1, 'In line ' . __LINE__);
    $this->assertEquals($address[1]['is_billing'], 1, 'In line ' . __LINE__);

    $this->assertEquals($address[2]['contact_id'], $contactId);
    $this->assertEquals($address[2]['is_primary'], 0, 'In line ' . __LINE__);
    $this->assertEquals($address[2]['is_billing'], 0, 'In line ' . __LINE__);

    $this->contactDelete($contactId);
  }

  /**
   * Add() 2 billing addresses, only the last one should be set as billing
   * Using the `CRM_Core_BAO_Address::add` mode
   *
   */
  public function testMultipleBillingAddressesCurrentmode(): void {
    $contactId = $this->individualCreate();

    $entityBlock = ['contact_id' => $contactId];
    $params = [];
    $params['contact_id'] = $contactId;
    $params['address']['1'] = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($params['address']['1']);
    CRM_Core_BAO_Address::writeRecord($params['address']['1']);

    // Add address 2
    $params['address']['2'] = [
      'street_address' => '120 Terminal Road',
      'supplemental_address_1' => 'A-wing:3037',
      'supplemental_address_2' => 'Bandra',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($params['address']['2']);
    CRM_Core_BAO_Address::writeRecord($params['address']['2']);

    $addresses = CRM_Core_BAO_Address::getValues($entityBlock);

    // Sort the multidimensional array by id
    usort($addresses, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    // Validate both results, remember that the keys have been reset to 0 after usort
    $this->assertEquals($addresses[0]['contact_id'], $contactId);
    $this->assertEquals($addresses[0]['is_primary'], 0, 'In line ' . __LINE__);
    $this->assertEquals($addresses[0]['is_billing'], 0, 'In line ' . __LINE__);

    $this->assertEquals($addresses[1]['contact_id'], $contactId);
    $this->assertEquals($addresses[1]['is_primary'], 1, 'In line ' . __LINE__);
    $this->assertEquals($addresses[1]['is_billing'], 1, 'In line ' . __LINE__);

    $this->contactDelete($contactId);
  }

  /**
   * AllAddress() method ( )
   */
  public function testallAddress(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );
    $fixParams = [
      'street_address' => 'SW 719B Beech Dr NW',
      'supplemental_address_1' => 'C/o OPDC',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'Neillsville',
      'postal_code' => '54456',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '44.553719',
      'geo_code_2' => '-90.61457',
      'location_type_id' => '2',
      'is_primary' => '',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $allAddress = CRM_Core_BAO_Address::allAddress($contactId);

    $this->assertEquals(count($allAddress), 2, 'Checking number of returned addresses.');

    $this->contactDelete($contactId);
  }

  /**
   * AllAddress() method ( ) with null value
   */
  public function testnullallAddress(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $contact_Id = NULL;

    $allAddress = CRM_Core_BAO_Address::allAddress($contact_Id);

    $this->assertEquals($allAddress, NULL, 'Checking null for returned addresses.');

    $this->contactDelete($contactId);
  }

  /**
   * GetValues() method (get Address fields)
   */
  public function testGetValues(): void {
    $contactId = $this->individualCreate();

    $params = [];
    $params['address']['1'] = [
      'street_address' => 'Oberoi Garden',
      'supplemental_address_1' => 'Attn: Accounting',
      'supplemental_address_2' => 'Powai',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
    ];

    $params['contact_id'] = $contactId;

    $fixAddress = TRUE;

    CRM_Core_BAO_Address::legacyCreate($params, $fixAddress);

    $addressId = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created address.'
    );

    $entityBlock = ['contact_id' => $contactId];
    $address = CRM_Core_BAO_Address::getValues($entityBlock);
    $this->assertEquals($address[1]['id'], $addressId);
    $this->assertEquals($address[1]['contact_id'], $contactId);
    $this->assertEquals($address[1]['state_province_abbreviation'], 'AL');
    $this->assertEquals($address[1]['state_province'], 'Alabama');
    $this->assertEquals($address[1]['country'], 'United States');
    $this->assertEquals($address[1]['street_address'], 'Oberoi Garden');
    $this->contactDelete($contactId);
  }

  /**
   * Enable street address parsing.
   *
   * @param string $status
   *
   * @throws \CRM_Core_Exception
   */
  public function setStreetAddressParsing($status) {
    $options = $this->callAPISuccess('Setting', 'getoptions', ['field' => 'address_options'])['values'];
    $address_options = reset($this->callAPISuccess('Setting', 'get', ['return' => 'address_options'])['values'])['address_options'];
    $parsingOption = array_search('Street Address Parsing', $options, TRUE);
    $optionKey = array_search($parsingOption, $address_options, FALSE);
    if ($status && !$optionKey) {
      $address_options[] = $parsingOption;
    }
    if (!$status && $optionKey) {
      unset($address_options[$optionKey]);
    }
    $this->callAPISuccess('Setting', 'create', ['address_options' => $address_options]);
  }

  /**
   * ParseStreetAddress if enabled, otherwise, don't.
   *
   * @throws \CRM_Core_Exception
   */
  public function testParseStreetAddressIfEnabled(): void {
    // Turn off address standardization. Parsing should work without it.
    Civi::settings()->set('address_standardization_provider', NULL);

    // Ensure street parsing happens if enabled.
    $this->setStreetAddressParsing(TRUE);

    $contactId = $this->individualCreate();
    $street_address = '54 Excelsior Ave.';
    $params = [
      'contact_id' => $contactId,
      'street_address' => $street_address,
      'location_type_id' => 1,
    ];

    $result = civicrm_api3('Address', 'create', $params);
    $value = array_pop($result['values']);
    $street_number = $value['street_number'] ?? NULL;
    $this->assertEquals($street_number, '54');

    // Ensure street parsing does not happen if disabled.
    $this->setStreetAddressParsing(FALSE);
    $result = civicrm_api3('Address', 'create', $params);
    $value = array_pop($result['values']);
    $street_number = $value['street_number'] ?? NULL;
    $this->assertEmpty($street_number);

  }

  /**
   * ParseStreetAddress() method (get street address parsed)
   */
  public function testParseStreetAddress(): void {

    // valid Street address to be parsed ( without locale )
    $street_address = "54A Excelsior Ave. Apt 1C";
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Excelsior Ave.');
    $this->assertEquals($parsedStreetAddress['street_unit'], 'Apt 1C');
    $this->assertEquals($parsedStreetAddress['street_number'], '54');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], 'A');

    // Out-of-range street number to be parsed.
    $street_address = '505050505050 Main St';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address);
    $this->assertEquals($parsedStreetAddress['street_name'], '');
    $this->assertEquals($parsedStreetAddress['street_unit'], '');
    $this->assertEquals($parsedStreetAddress['street_number'], '');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], '');

    // valid Street address to be parsed ( $locale = 'en_US' )
    $street_address = "54A Excelsior Ave. Apt 1C";
    $locale = 'en_US';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Excelsior Ave.');
    $this->assertEquals($parsedStreetAddress['street_unit'], 'Apt 1C');
    $this->assertEquals($parsedStreetAddress['street_number'], '54');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], 'A');

    // invalid Street address ( $locale = 'en_US' )
    $street_address = "West St. Apt 1";
    $locale = 'en_US';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'West St.');
    $this->assertEquals($parsedStreetAddress['street_unit'], 'Apt 1');
    $this->assertNotContains('street_number', $parsedStreetAddress);
    $this->assertNotContains('street_number_suffix', $parsedStreetAddress);

    // valid Street address to be parsed ( $locale = 'fr_CA' )
    $street_address = "2-123CA Main St";
    $locale = 'fr_CA';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Main St');
    $this->assertEquals($parsedStreetAddress['street_unit'], '2');
    $this->assertEquals($parsedStreetAddress['street_number'], '123');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], 'CA');

    // invalid Street address ( $locale = 'fr_CA' )
    $street_address = "123 Main St";
    $locale = 'fr_CA';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Main St');
    $this->assertEquals($parsedStreetAddress['street_number'], '123');
    $this->assertNotContains('street_unit', $parsedStreetAddress);
    $this->assertNotContains('street_number_suffix', $parsedStreetAddress);
  }

  /**
   * @dataProvider supportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnTrueForSupportedLocales($locale) {
    $isSupported = CRM_Core_BAO_Address::isSupportedParsingLocale($locale);
    $this->assertTrue($isSupported);
  }

  /**
   * @dataProvider supportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnTrueForSupportedDefaultLocales($locale) {
    CRM_Core_Config::singleton()->lcMessages = $locale;
    $isSupported = CRM_Core_BAO_Address::isSupportedParsingLocale();
    $this->assertTrue($isSupported);

  }

  public static function supportedAddressParsingLocales() {
    return [
      ['en_US'],
      ['en_CA'],
      ['fr_CA'],
    ];
  }

  /**
   * @dataProvider sampleOFUnsupportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnFalseForUnSupportedLocales($locale) {
    $isNotSupported = CRM_Core_BAO_Address::isSupportedParsingLocale($locale);
    $this->assertFalse($isNotSupported);
  }

  /**
   * @dataProvider sampleOFUnsupportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnFalseForUnSupportedDefaultLocales($locale) {
    CRM_Core_Config::singleton()->lcMessages = $locale;
    $isNotSupported = CRM_Core_BAO_Address::isSupportedParsingLocale();
    $this->assertFalse($isNotSupported);
  }

  public static function sampleOFUnsupportedAddressParsingLocales() {
    return [
      ['en_GB'],
      ['af_ZA'],
      ['da_DK'],
    ];
  }

  /**
   * CRM-21214 - Ensure all child addresses are updated correctly - 1.
   * 1. First, create three contacts: A, B, and C
   * 2. Create an address for contact A
   * 3. Use contact A's address for contact B
   * 4. Use contact B's address for contact C
   * 5. Change contact A's address
   * Address of Contact C should reflect contact A's address change
   * Also, Contact C's address' master_id should be Contact A's address id.
   */
  public function testSharedAddressChaining1(): void {
    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);
    $contactIdC = $this->individualCreate([], 2);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdB,
    ];
    $addAddressB = CRM_Core_BAO_Address::writeRecord($addressParamsB);

    $addressParamsC = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressB->id,
      'contact_id' => $contactIdC,
    ];
    $addAddressC = CRM_Core_BAO_Address::writeRecord($addressParamsC);

    $updatedAddressParamsA = [
      'id' => $addAddressA->id,
      'street_address' => '1313 New Address Lane',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $updatedAddressA = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsA);

    // CRM-21214 - Has Address C been updated with Address A's new values?
    $newAddressC = new CRM_Core_DAO_Address();
    $newAddressC->id = $addAddressC->id;
    $newAddressC->find(TRUE);
    $newAddressC->fetch(TRUE);

    $this->assertEquals($updatedAddressA->street_address, $newAddressC->street_address);
    $this->assertEquals($updatedAddressA->id, $newAddressC->master_id);
  }

  /**
   * CRM-21214 - Ensure all child addresses are updated correctly - 2.
   * 1. First, create three contacts: A, B, and C
   * 2. Create an address for contact A and B
   * 3. Use contact A's address for contact C
   * 4. Use contact B's address for contact A
   * 5. Change contact B's address
   * Address of Contact C should reflect contact B's address change
   * Also, Contact C's address' master_id should be Contact B's address id.
   */
  public function testSharedAddressChaining2(): void {
    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);
    $contactIdC = $this->individualCreate([], 2);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdB,
    ];
    $addAddressB = CRM_Core_BAO_Address::writeRecord($addressParamsB);

    $addressParamsC = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdC,
    ];
    $addAddressC = CRM_Core_BAO_Address::writeRecord($addressParamsC);

    $updatedAddressParamsA = [
      'id' => $addAddressA->id,
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressB->id,
      'contact_id' => $contactIdA,
    ];
    $updatedAddressA = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsA);

    $updatedAddressParamsB = [
      'id' => $addAddressB->id,
      'street_address' => '1313 New Address Lane',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdB,
    ];
    $updatedAddressB = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsB);

    // CRM-21214 - Has Address C been updated with Address B's new values?
    $newAddressC = new CRM_Core_DAO_Address();
    $newAddressC->id = $addAddressC->id;
    $newAddressC->find(TRUE);
    $newAddressC->fetch(TRUE);

    $this->assertEquals($updatedAddressB->street_address, $newAddressC->street_address);
    $this->assertEquals($updatedAddressB->id, $newAddressC->master_id);
  }

  /**
   * CRM-21214 - Ensure all child addresses are updated correctly - 3.
   * 1. First, create a contact: A
   * 2. Create an address for contact A
   * 3. Use contact A's address for contact A's address
   * An error should be given, and master_id should remain the same.
   */
  public function testSharedAddressChaining3(): void {
    $contactIdA = $this->individualCreate([], 0);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $updatedAddressParamsA = [
      'id' => $addAddressA->id,
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdA,
    ];
    $updatedAddressA = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsA);

    // CRM-21214 - AdressA shouldn't be master of itself.
    $this->assertEmpty($updatedAddressA->master_id);
  }

  /**
   * Ensure shared address updates work in opposite direction
   *
   * If Person A shares their address with Person B and person B updates
   * the address, ensure it is updated in Person A's record.
   */
  public function testSharedAddressReverseUpdate(): void {
    // Create two contacts. ContactA shares their address with ContactB.
    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = \Civi\Api4\Address::create(FALSE);
    foreach ($addressParamsA as $key => $value) {
      $addAddressA = $addAddressA->addValue($key, $value);
    }
    $addressA = $addAddressA->execute()->first();

    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addressA['id'],
      'contact_id' => $contactIdB,
    ];
    $addAddressB = \Civi\Api4\Address::create(FALSE);
    foreach ($addressParamsB as $key => $value) {
      $addAddressB = $addAddressB->addValue($key, $value);
    }
    $addressB = $addAddressB->execute()->first();

    // Update ContactB's address.
    $updateAddressParamsB = [
      'id' => $addressB['id'],
      'street_address' => '456 New Street St.',
      'master_id' => $addressA['id'],
    ];
    $updateAddressB = \Civi\Api4\Address::update(FALSE);
    foreach ($updateAddressParamsB as $key => $value) {
      $updateAddressB = $updateAddressB->addValue($key, $value);
    }
    $updatedAddressB = $updateAddressB->execute()->first();

    // Is the update reflected in ContactA's address?
    $updatedAddressA = \Civi\Api4\Address::get(FALSE)
      ->addSelect('street_address')
      ->addWhere('id', '=', $addressA['id'])
      ->execute()->first();

    $this->assertEquals($updatedAddressB['street_address'], $updatedAddressA['street_address']);

    // Update ContactB's address again, but leave out the master id. We should still
    // update the master id address record, even if we leave it out (e.g. could be a
    // profile that doesn't include the master id value).
    $updateAddressParamsB = [
      'id' => $addressB['id'],
      'street_address' => '456 New Street St.',
    ];
    $updateAddressB = \Civi\Api4\Address::update(FALSE);
    foreach ($updateAddressParamsB as $key => $value) {
      $updateAddressB = $updateAddressB->addValue($key, $value);
    }
    $updatedAddressB = $updateAddressB->execute()->first();

    // Is the update reflected in ContactA's address?
    $updatedAddressA = \Civi\Api4\Address::get(FALSE)
      ->addSelect('street_address')
      ->addWhere('id', '=', $addressA['id'])
      ->execute()->first();

    $this->assertEquals($updatedAddressB['street_address'], $updatedAddressA['street_address']);

    // Update ContactB's address again, but set master id to NULL. We should not
    // update the master id address record.
    $updateAddressParamsB = [
      'id' => $addressB['id'],
      'street_address' => '890 I Am Moving Out St.',
      'master_id' => NULL,
    ];
    $updateAddressB = \Civi\Api4\Address::update(FALSE);
    foreach ($updateAddressParamsB as $key => $value) {
      $updateAddressB = $updateAddressB->addValue($key, $value);
    }
    $updatedAddressB = $updateAddressB->execute()->first();

    // The update should not be reflected in ContactA's address
    $updatedAddressA = \Civi\Api4\Address::get(FALSE)
      ->addSelect('street_address')
      ->addWhere('id', '=', $addressA['id'])
      ->execute()->first();

    $this->assertNotEquals($updatedAddressB['street_address'], $updatedAddressA['street_address']);

  }

  /**
   * dev/dev/core#1670 - Ensure that the custom fields on adresses are copied
   * to inherited address
   * 1. test the creation of the shared address with custom field
   * 2. test the update of the custom field in the master
   */
  public function testSharedAddressCustomField(): void {

    $this->createCustomGroupWithFieldOfType(['extends' => 'Address'], 'text');
    $customField = $this->getCustomFieldName('text');

    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
      $customField => 'this is a custom text field',
    ];
    $addressParamsA['custom'] = CRM_Core_BAO_CustomField::postProcess($addressParamsA, NULL, 'Address');

    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    // without having the custom field, we should still copy the values from master
    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdB,
    ];
    $addAddressB = CRM_Core_BAO_Address::writeRecord($addressParamsB);

    // 1. check if the custom fields values have been copied from master to shared address
    $address = $this->callAPISuccessGetSingle('Address', ['id' => $addAddressB->id, 'return' => $this->getCustomFieldName('text')]);
    $this->assertEquals($addressParamsA[$customField], $address[$customField]);

    // 2. now, we update addressA custom field to see if it goes into addressB
    $addressParamsA['id'] = $addAddressA->id;
    $addressParamsA[$customField] = 'updated custom text field';
    $addressParamsA['custom'] = CRM_Core_BAO_CustomField::postProcess($addressParamsA, NULL, 'Address');
    CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $address = $this->callAPISuccessGetSingle('Address', ['id' => $addAddressB->id, 'return' => $this->getCustomFieldName('text')]);
    $this->assertEquals($addressParamsA[$customField], $address[$customField]);

  }

  /**
   * Pinned countries with Default country
   */
  public function testPinnedCountriesWithDefaultCountry(): void {
    // Guyana, Netherlands, United States
    $pinnedCountries = ['1093', '1152', '1228'];

    // set default country to Netherlands
    $this->callAPISuccess('Setting', 'create', ['defaultContactCountry' => 1152, 'pinnedContactCountries' => $pinnedCountries]);
    // get the list of country
    $availableCountries = CRM_Core_PseudoConstant::country(FALSE, FALSE);
    // get the order of country id using their keys
    $availableCountries = array_keys($availableCountries);

    // default country is set, so first country should be Netherlands, then rest from pinned countries.

    // Netherlands
    $this->assertEquals(1152, $availableCountries[0]);
    // Guyana
    $this->assertEquals(1093, $availableCountries[1]);
    // United States
    $this->assertEquals(1228, $availableCountries[2]);
  }

  /**
   * Pinned countries with out Default country
   */
  public function testPinnedCountriesWithOutDefaultCountry(): void {
    // Guyana, Netherlands, United States
    $pinnedCountries = ['1093', '1152', '1228'];

    // unset default country
    $this->callAPISuccess('Setting', 'create', ['defaultContactCountry' => NULL, 'pinnedContactCountries' => $pinnedCountries]);

    // get the list of country
    $availableCountries = CRM_Core_PseudoConstant::country(FALSE, FALSE);
    // get the order of country id using their keys
    $availableCountries = array_keys($availableCountries);

    // no default country, so sequnece should be present as per pinned countries.

    // Guyana
    $this->assertEquals(1093, $availableCountries[0]);
    // Netherlands
    $this->assertEquals(1152, $availableCountries[1]);
    // United States
    $this->assertEquals(1228, $availableCountries[2]);
  }

  public function testPinnedCountryWithEntity(): void {
    \Civi::settings()->set('pinnedContactCountries', ['1228']);
    $countries = \Civi::entity('Address')->getOptions('country_id');
    $this->assertEquals('US', $countries[0]['name']);
  }

  public function testCountryLabelTranslation(): void {
    CRM_Core_I18n::singleton()->setLocale('nl_NL');
    $countries = \Civi::entity('Address')->getOptions('country_id');
    $checked = [];
    foreach ($countries as $country) {
      switch ($country['name']) {
        case 'NL':
          $this->assertEquals('Nederland', $country['label']);
          $checked[] = 'NL';
          break;

        case 'US':
          $this->assertEquals('Verenigde Staten', $country['label']);
          $checked[] = 'US';
          break;
      }
    }
    $this->assertCount(2, $checked, 'Country list incomplete');
  }

  public function testCountrySorting(): void {
    $countries = \Civi::entity('Address')->getOptions('country_id');
    $this->assertEquals('AF', $countries[0]['name']);
    // Åland Islands should sort second in en_US locale
    $this->assertEquals('AX', $countries[1]['name']);
    $this->assertEquals('AL', $countries[2]['name']);
    $this->assertEquals('ZW', array_pop($countries)['name']);

    CRM_Core_I18n::singleton()->setLocale('nl_NL');
    $countries = \Civi::entity('Address')->getOptions('country_id');
    $this->assertEquals('AF', $countries[0]['name']);
    // Åland Islands
    $this->assertEquals('AX', $countries[1]['name']);
    $this->assertEquals('AL', $countries[2]['name']);
    $this->assertEquals('US', $countries[237]['name']);
    $this->assertEquals('CH', array_pop($countries)['name']);

    CRM_Core_I18n::singleton()->setLocale('it_IT');
    $countries = \Civi::entity('Address')->getOptions('country_id');
    $this->assertEquals('AF', $countries[0]['name']);
    $this->assertEquals('AL', $countries[1]['name']);
    $this->assertEquals('DZ', $countries[2]['name']);
    // Åland Islands
    $this->assertEquals('AX', $countries[104]['name']);
    $this->assertEquals('US', $countries[213]['name']);
    $this->assertEquals('ZW', array_pop($countries)['name']);
  }

  /**
   * Test dev/core#2379 fix - geocodes shouldn't be > 14 characters.
   */
  public function testLongGeocodes(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '41.701308979563',
      'geo_code_2' => '-73.91941868639',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    $addAddress = CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $this->assertEquals('41.70130897956', $addAddress->geo_code_1, 'In line' . __LINE__);
    $this->assertEquals('-73.9194186863', $addAddress->geo_code_2, 'In line' . __LINE__);
  }

}
