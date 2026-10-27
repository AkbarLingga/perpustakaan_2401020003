<?php

test('the home page redirects to the book list', function () {
    $response = $this->get('/');

    $response->assertRedirect('/buku');
});
