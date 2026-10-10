<?php

uses(Tests\TestCase::class);

test('phpunit forcibly keeps tests on the isolated sqlite database', function () {
    expect(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(getenv('DB_CONNECTION'))->toBe('sqlite')
        ->and(getenv('DB_DATABASE'))->toBe(':memory:')
        ->and(getenv('DB_URL'))->toBe('');
});
