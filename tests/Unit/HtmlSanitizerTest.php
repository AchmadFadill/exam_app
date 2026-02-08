<?php

use App\Support\HtmlSanitizer;

it('removes dangerous scripts and event handlers but keeps allowed formatting tags', function () {
    $dirty = '<p><b>Bold</b> and <i>Italic</i> <script>alert(1)</script><img src="https://example.test/image.png" onerror="alert(1)" alt="x"></p>';

    $clean = HtmlSanitizer::clean($dirty);

    expect($clean)->toContain('<b>Bold</b>')
        ->and($clean)->toContain('<i>Italic</i>')
        ->and($clean)->toContain('<img')
        ->and($clean)->toContain('src="https://example.test/image.png"')
        ->and($clean)->not->toContain('<script')
        ->and($clean)->not->toContain('onerror=');
});

it('blocks javascript urls in links', function () {
    $dirty = '<a href="javascript:alert(1)">Click</a>';

    $clean = HtmlSanitizer::clean($dirty);

    expect($clean)->toContain('Click')
        ->and($clean)->not->toContain('javascript:');
});
