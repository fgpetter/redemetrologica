<?php

use App\Models\User;

it('logs out through the custom middleware without invalidating the session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['logout-test-data' => 'preserved'])
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
    $response->assertSessionHas('logout-test-data', 'preserved');
});
