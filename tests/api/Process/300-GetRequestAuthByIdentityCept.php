<?php

$I = new ApiTester($scenario);
$I->wantTo('get a process, using identity for auth');

$path = '/processes/4527288f-108e-fk69-8d2d-7914ffd93894';
$headers = ['date' => date(DATE_RFC1123)];
$request = $I->getSignedRequest('get', $path, $headers);

$I->haveHttpHeader('X-Identity', '6uk7288s-afe4-7398-8dbh-7914ffd930pl');
$I->haveHttpHeader('date', $request->getHeaderLine('date'));
$I->haveHttpHeader('authorization', $request->getHeaderLine('authorization'));

$I->sendGET($path);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

// TODO Move this to API helper
$expectedJson = file_get_contents(__DIR__ . '/../../_data/processes/basic-user-and-system.json');
$expected = json_decode($expectedJson, true);

$I->seeResponseContainsJson($expected);