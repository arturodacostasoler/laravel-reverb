<?php

use Laravel\Reverb\Tests\ReverbTestCase;

use function React\Async\await;

uses(ReverbTestCase::class);

it('can receive an event batch trigger', function () {
    $response = await($this->signedPostRequest('batch_events', ['batch' => [
        [
            'name' => 'NewEvent',
            'channel' => 'test-channel',
            'data' => json_encode(['some' => 'data']),
        ],
    ]]));

    expect($response->getStatusCode())->toBe(200);
    expect($response->getBody()->getContents())->toBe('{"batch":{}}');
});

it('can receive an event batch trigger with multiple events', function () {
    $response = await($this->signedPostRequest('batch_events', ['batch' => [
        [
            'name' => 'NewEvent',
            'channel' => 'test-channel',
            'data' => json_encode(['some' => 'data']),
        ],
        [
            'name' => 'AnotherNewEvent',
            'channel' => 'test-channel-two',
            'data' => json_encode(['some' => ['more' => 'data']]),
        ],
    ]]));

    expect($response->getStatusCode())->toBe(200);
    expect($response->getBody()->getContents())->toBe('{"batch":{}}');
});

it('can receive an event batch trigger with multiple events and return info for each', function () {
    subscribe('presence-test-channel');
    subscribe('test-channel-two');
    subscribe('test-channel-three');
    $response = await($this->signedPostRequest('batch_events', ['batch' => [
        [
            'name' => 'NewEvent',
            'channel' => 'presence-test-channel',
            'data' => json_encode(['some' => 'data']),
            'info' => 'user_count',
        ],
        [
            'name' => 'AnotherNewEvent',
            'channel' => 'test-channel-two',
            'data' => json_encode(['some' => ['more' => 'data']]),
            'info' => 'subscription_count',
        ],
        [
            'name' => 'YetAnotherNewEvent',
            'channel' => 'test-channel-three',
            'data' => json_encode(['some' => ['more' => 'data']]),
            'info' => 'subscription_count,user_count',
        ],
    ]]));

    expect($response->getStatusCode())->toBe(200);
    expect($response->getBody()->getContents())->toBe('{"batch":[{"user_count":1},{"subscription_count":1},{"subscription_count":1}]}');
});

it('can receive an event batch trigger with multiple events and return info for some', function () {
    subscribe('presence-test-channel');
    $response = await($this->signedPostRequest('batch_events', ['batch' => [
        [
            'name' => 'NewEvent',
            'channel' => 'presence-test-channel',
            'data' => json_encode(['some' => 'data']),
            'info' => 'user_count',
        ],
        [
            'name' => 'AnotherNewEvent',
            'channel' => 'test-channel-two',
            'data' => json_encode(['some' => ['more' => 'data']]),
        ],
    ]]));

    expect($response->getStatusCode())->toBe(200);
    expect($response->getBody()->getContents())->toBe('{"batch":[{"user_count":1},{}]}');
});