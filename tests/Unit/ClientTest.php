<?php

use AdaiasMagdiel\G1\Client;
use AdaiasMagdiel\G1\Enum\Estado;
use AdaiasMagdiel\G1\Response\Ultimas;

const CACHE_DIR = __DIR__ . "/.g1-cache-test";

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

test('fetches latest news without state', function () {
    $client = new Client(cacheDir: CACHE_DIR);
    $ultimas = $client->ultimas();

    expect($ultimas)->toBeInstanceOf(Ultimas::class);
    expect($ultimas->id)->toBeString();
    expect($ultimas->nextPage)->toBe(2);
    expect($ultimas->news)->toBeArray();
    expect($ultimas->news[0])->toBeInstanceOf(\AdaiasMagdiel\G1\Response\News::class);
    expect($ultimas->news[0]->title)->toBeString();
});

test('fetches latest news with a specific state', function () {
    $client = new Client(cacheDir: CACHE_DIR);
    $ultimas = $client->ultimas(estado: Estado::PARA);

    expect($ultimas)->toBeInstanceOf(Ultimas::class);
    expect($ultimas->id)->toBeString();
    expect($ultimas->nextPage)->toBe(2);
    expect($ultimas->news)->toBeArray();
    expect($ultimas->news[0])->toBeInstanceOf(\AdaiasMagdiel\G1\Response\News::class);
    expect($ultimas->news[0]->title)->toBeString();
});

test('fetches latest news with specific page', function () {
    $client = new Client(cacheDir: CACHE_DIR);
    $ultimas = $client->ultimas(page: 5);

    expect($ultimas)->toBeInstanceOf(Ultimas::class);
    expect($ultimas->id)->toBeString();
    expect($ultimas->nextPage)->toBe(6);
    expect($ultimas->news)->toBeArray();
    expect($ultimas->news[0])->toBeInstanceOf(\AdaiasMagdiel\G1\Response\News::class);
    expect($ultimas->news[0]->title)->toBeString();
});

test('fetches latest news with specific page and specific state', function () {
    $client = new Client(cacheDir: CACHE_DIR);
    $ultimas = $client->ultimas(page: 10, estado: Estado::ESPIRITO_SANTO);

    expect($ultimas)->toBeInstanceOf(Ultimas::class);
    expect($ultimas->id)->toBeString();
    expect($ultimas->nextPage)->toBe(11);
    expect($ultimas->news)->toBeArray();
    expect($ultimas->news[0])->toBeInstanceOf(\AdaiasMagdiel\G1\Response\News::class);
    expect($ultimas->news[0]->title)->toBeString();
});
