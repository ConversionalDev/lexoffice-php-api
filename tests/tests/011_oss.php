<?php
test_start('test taxrates unknown country');
try {
    $request = $lexoffice->get_taxrates('ZZ', strtotime('2021-07-05'));
    if (empty($request)) {
        test_finished(true);
    } else {
        var_dump($request);
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test taxrates country DE');
try {
    $request = $lexoffice->get_taxrates('DE', strtotime('2021-07-05'));
    if (!empty($request) && $request['default'] == 19 && in_array(7, $request['reduced'])) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test taxrates country nl - before oss');
try {
    $request = $lexoffice->get_taxrates('nl', strtotime('2021-06-05'));
    if (!empty($request) && $request['default'] == 19 && in_array(7, $request['reduced'])) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test taxrates country nl - after oss');
try {
    $request = $lexoffice->get_taxrates('nl', strtotime('2021-07-05'));
    if (!empty($request) && $request['default'] == 21 && in_array(9, $request['reduced'])) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test oss settings - DE');
try {
    $request = $lexoffice->is_oss_needed('DE', strtotime('2021-07-05'));
    if ($request === false) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test oss settings - NL');
try {
    $request = $lexoffice->is_oss_needed('NL', strtotime('2021-07-05'));
    if ($request === 'destination') {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test oss settings - NL - before oss');
try {
    $request = $lexoffice->is_oss_needed('NL', strtotime('2021-06-05'));
    if ($request === false) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test oss settings - ZZ');
try {
    $request = $lexoffice->is_oss_needed('ZZ', strtotime('2021-07-05'));
    if ($request === false) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test oss settings - GB');
try {
    $request = $lexoffice->is_oss_needed('GB', strtotime('2021-07-05'));
    if ($request === false) {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('test oss voucher category - GB');
try {
    $request = $lexoffice->get_oss_voucher_category('GB', strtotime('2021-07-05'));
    test_finished(false);
}
catch (lexoffice_exception $e) {
    if ($e->getMessage() === 'lexoffice-php-api: no possible SSO voucher category id') {
        test_finished(true);
    } else {
        test_finished(false);
    }
}

test_start('test oss voucher category - DE');
try {
    $request = $lexoffice->get_oss_voucher_category('DE', strtotime('2021-07-05'));
    test_finished(false);
}
catch (lexoffice_exception $e) {
    if ($e->getMessage() === 'lexoffice-php-api: no possible SSO voucher category id') {
        test_finished(true);
    } else {
        test_finished(false);
    }
}

test_start('test oss voucher category - NL');
try {
    $request = $lexoffice->get_oss_voucher_category('NL', strtotime('2021-07-05'));
    if ($request === '4ebd965a-7126-416c-9d8c-a5c9366ee473') {
        test_finished(true);
    } else {
        test_finished(false);
    }
}
catch (lexoffice_exception $e) {
    test($e->getMessage());
    test_finished(false);
}

test_start('create netto oss voucher 13% / 23% Portugal with get_oss_voucher_category()');
try {
    // create contact
    $random_contact_name = 'contact_'.rand(11111111, 999999999999);
    $request = $lexoffice->create_contact(array(
        'version' => 0,
        'roles' => array(
            'customer' => array(
                'number' => '',
            ),
        ),
        'company' => array(
            'name' => $random_contact_name,
            'street' => 'Genklerhardt 6',
            'zip' => '51647',
            'city' => 'Gummersbach',
            'countryCode' => 'PT',
        ),
        'addresses' => array(
            'billing' => array(
                array(
                    'street' => 'Genklerhardt 6',
                    'zip' => '51647',
                    'city' => 'Gummersbach',
                    'countryCode' => 'PT',
                ),
            ),
        ),
    ));

    // create voucher
    $request = $lexoffice->create_voucher([
        'version' => 0,
        'voucherDate' => '2021-07-05',
        'dueDate' => '2021-07-10',
        'type' => 'salesinvoice',
        'voucherNumber' => "21-001003RP",
        'contactId' => $request->id,
        'totalGrossAmount' => 236.00,
        'voucherItems' => [
            [
                'amount' => 100.00,
                'taxAmount' => 23.00,
                'taxRatePercent' => 23,
                'categoryId' => $lexoffice->get_oss_voucher_category('pt', strtotime('2021-07-05'), 1),
            ],
            [
                'amount' => 100.00,
                'taxAmount' => 13.00,
                'taxRatePercent' => 13,
                'categoryId' => $lexoffice->get_oss_voucher_category('pt', strtotime('2021-07-05'), 1),
            ],
        ],
        'taxType' => "net",
        'totalTaxAmount' => 36.00,
    ]);

    if ($request->id) {
        test('voucher created - id: '.$request->id);
        test_finished(true);
    } else {
        test_finished(false);
    }
} catch(lexoffice_exception $e) {
    test($e->getMessage());
    test(print_r($e->get_error(), true));
    test_finished(false);
}

test_start('create brutto oss voucher 13% / 23% Portugal with get_oss_voucher_category()');
try {
    // create contact
    $random_contact_name = 'contact_'.rand(11111111, 999999999999);
    $request = $lexoffice->create_contact(array(
        'version' => 0,
        'roles' => array(
            'customer' => array(
                'number' => '',
            ),
        ),
        'company' => array(
            'name' => $random_contact_name,
            'street' => 'Genklerhardt 6',
            'zip' => '51647',
            'city' => 'Gummersbach',
            'countryCode' => 'PT',
        ),
        'addresses' => array(
            'billing' => array(
                array(
                    'street' => 'Genklerhardt 6',
                    'zip' => '51647',
                    'city' => 'Gummersbach',
                    'countryCode' => 'PT',
                ),
            ),
        ),
    ));

    // create voucher
    $request = $lexoffice->create_voucher([
        'version' => 0,
        'voucherDate' => '2021-07-05',
        'dueDate' => '2021-07-10',
        'type' => 'salesinvoice',
        'voucherNumber' => "21-001003RP",
        'contactId' => $request->id,
        'totalGrossAmount' => 236.00,
        'voucherItems' => [
            [
                'amount' => 123.00,
                'taxAmount' => 23.00,
                'taxRatePercent' => 23,
                'categoryId' => $lexoffice->get_oss_voucher_category('pt', strtotime('2021-07-05'), 1),
            ],
            [
                'amount' => 113.00,
                'taxAmount' => 13.00,
                'taxRatePercent' => 13,
                'categoryId' => $lexoffice->get_oss_voucher_category('pt', strtotime('2021-07-05'), 1),
            ],
        ],
        'taxType' => "gross",
        'totalTaxAmount' => 36.00,
    ]);

    if ($request->id) {
        test('voucher created - id: '.$request->id);
        test_finished(true);
    } else {
        test_finished(false);
    }
} catch(lexoffice_exception $e) {
    test($e->getMessage());
    test(print_r($e->get_error(), true));
    test_finished(false);
}

test_start('create netto oss voucher 13% / 23% Portugal with get_needed_voucher_booking_id()');
try {
    // create contact
    $random_contact_name = 'contact_'.rand(11111111, 999999999999);
    $request = $lexoffice->create_contact(array(
        'version' => 0,
        'roles' => array(
            'customer' => array(
                'number' => '',
            ),
        ),
        'company' => array(
            'name' => $random_contact_name,
            'street' => 'Genklerhardt 6',
            'zip' => '51647',
            'city' => 'Gummersbach',
            'countryCode' => 'PT',
        ),
        'addresses' => array(
            'billing' => array(
                array(
                    'street' => 'Genklerhardt 6',
                    'zip' => '51647',
                    'city' => 'Gummersbach',
                    'countryCode' => 'PT',
                ),
            ),
        ),
    ));

    // create voucher
    $request = $lexoffice->create_voucher([
        'version' => 0,
        'voucherDate' => '2021-07-05',
        'dueDate' => '2021-07-10',
        'type' => 'salesinvoice',
        'voucherNumber' => "21-001003RP",
        'contactId' => $request->id,
        'totalGrossAmount' => 236.00,
        'voucherItems' => [
            [
                'amount' => 100.00,
                'taxAmount' => 23.00,
                'taxRatePercent' => 23,
                'categoryId' => $lexoffice->get_needed_voucher_booking_id(23, 'PT', strtotime('2021-07-05'), false, false, true),
            ],
            [
                'amount' => 100.00,
                'taxAmount' => 13.00,
                'taxRatePercent' => 13,
                'categoryId' => $lexoffice->get_needed_voucher_booking_id(13, 'PT', strtotime('2021-07-05'), false, false, true),
            ],
        ],
        'taxType' => "net",
        'totalTaxAmount' => 36.00,
    ]);

    if ($request->id) {
        test('voucher created - id: '.$request->id);
        test_finished(true);
    } else {
        test_finished(false);
    }
} catch(lexoffice_exception $e) {
    test($e->getMessage());
    test(print_r($e->get_error(), true));
    test_finished(false);
}

test_start('create brutto oss voucher 13% / 23% Portugal with get_needed_voucher_booking_id()');
try {
    // create contact
    $random_contact_name = 'contact_'.rand(11111111, 999999999999);
    $request = $lexoffice->create_contact(array(
        'version' => 0,
        'roles' => array(
            'customer' => array(
                'number' => '',
            ),
        ),
        'company' => array(
            'name' => $random_contact_name,
            'street' => 'Genklerhardt 6',
            'zip' => '51647',
            'city' => 'Gummersbach',
            'countryCode' => 'PT',
        ),
        'addresses' => array(
            'billing' => array(
                array(
                    'street' => 'Genklerhardt 6',
                    'zip' => '51647',
                    'city' => 'Gummersbach',
                    'countryCode' => 'PT',
                ),
            ),
        ),
    ));

    // create voucher
    $request = $lexoffice->create_voucher([
        'version' => 0,
        'voucherDate' => '2021-07-05',
        'dueDate' => '2021-07-10',
        'type' => 'salesinvoice',
        'voucherNumber' => "21-001003RP",
        'contactId' => $request->id,
        'totalGrossAmount' => 236.00,
        'voucherItems' => [
            [
                'amount' => 123.00,
                'taxAmount' => 23.00,
                'taxRatePercent' => 23,
                'categoryId' => $lexoffice->get_needed_voucher_booking_id(23, 'PT', strtotime('2021-07-05'), false, false, true),
            ],
            [
                'amount' => 113.00,
                'taxAmount' => 13.00,
                'taxRatePercent' => 13,
                'categoryId' => $lexoffice->get_needed_voucher_booking_id(13, 'PT', strtotime('2021-07-05'), false, false, true),
            ],
        ],
        'taxType' => "gross",
        'totalTaxAmount' => 36.00,
    ]);

    if ($request->id) {
        test('voucher created - id: '.$request->id);
        test_finished(true);
    } else {
        test_finished(false);
    }
} catch(lexoffice_exception $e) {
    test($e->getMessage());
    test(print_r($e->get_error(), true));
    test_finished(false);
}