<?php

use Laravel\Dusk\Browser;

test('basic example', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->waitForText('CBT System', 10)
                ->assertSee('CBT System');
    });
});
