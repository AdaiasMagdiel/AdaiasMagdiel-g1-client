<?php

use AdaiasMagdiel\G1\Cache;

const CACHE_DIR = __DIR__ . "/.gi-test-cache";

beforeEach(function () {
    $this->cache = new Cache(path: CACHE_DIR);

    $this->key = "KEY_";
    $this->value = "DEFAULT_VALUE";
    $this->expires = 10;
});

afterAll(function () {
    function removeDir($dir)
    {
        $arquivos = scandir($dir);
        foreach ($arquivos as $arquivo) {
            if ($arquivo != '.' && $arquivo != '..') {
                $caminho = $dir . '/' . $arquivo;
                if (is_file($caminho)) {
                    unlink($caminho);
                } elseif (is_dir($caminho)) {
                    removeDir($caminho);
                }
            }
        }
        rmdir($dir);
    }

    removeDir(CACHE_DIR);
});

it('creates cache directory if it does not exist', function () {
    expect(is_dir($this->cache->path))->toBeTrue();
});

it('stores and retrieves a cached value correctly', function () {
    $this->cache->set($this->key . "1", $this->value, $this->expires);

    $retrievedValue = $this->cache->get($this->key . "1", fn() => 'new-value');
    expect($retrievedValue)->toBe($this->value);
});

it('calls action and updates cache if key does not exist', function () {
    $action = fn() => 'generated-value';

    $retrievedValue = $this->cache->get($this->key  . "2", $action, $this->expires);
    expect($retrievedValue)->toBe('generated-value');

    $retrievedValueAgain = $this->cache->get($this->key  . "2", fn() => 'another-value');
    expect($retrievedValueAgain)->toBe('generated-value');
});

it('resets cache if expired and updates with new action result', function () {
    $this->cache->set($this->key . "3", $this->value, 1);
    sleep(2);

    $action = fn() => 'new-action-value';
    $retrievedValue = $this->cache->get($this->key . "3", $action, $this->expires);

    expect($retrievedValue)->toBe('new-action-value');
});

it('does not expire cache if expiration is set to -1', function () {
    $this->cache->set($this->key . "4", $this->value, -1);

    sleep(2);

    $retrievedValue = $this->cache->get($this->key . "4", fn() => 'new-value');
    expect($retrievedValue)->toBe($this->value);
});

it('handles empty cache file by resetting with action result', function () {
    $this->cache->set($this->key . "5", $this->value, $this->expires);
    $retrievedValue = $this->cache->get(
        $this->key . "5",
        fn() => 'another-value',
        $this->expires
    );
    expect($retrievedValue)->toBe($this->value);

    $filepath = $this->cache->getFilepathFromKey($this->key . "5");
    file_put_contents($filepath, '');

    $action = fn() => 'value-after-empty';
    $retrievedValue = $this->cache->get($this->key . "5", $action, $this->expires);

    expect($retrievedValue)->toBe('value-after-empty');
});
