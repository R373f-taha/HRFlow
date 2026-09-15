<?php

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withToken;

describe('Authentication', function () {


describe('Login', function () {
test('user can login with valid credentials', function () {
    // Arrange
    User::factory()->create([
        'email' => 'admin@hrflow.test',
        'password' => 'password',
        'is_active' => true,
    ]);

    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'admin@hrflow.test',
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Login successful.')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                ],
                'token',
            ],
        ]);
});

test('user cannot login with wrong password', function () {
    // Arrange
    User::factory()->create([
        'email' => 'admin@hrflow.test',
        'password' => 'password',
        'is_active' => true,
    ]);

    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'admin@hrflow.test',
        'password' => 'wrong-password',
    ]);

    // Assert
    $response
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials.',
            'data' => null,
        ]);
});
test('user cannot login with unknown email', function () {
    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'unknown@hrflow.test',
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials.',
            'data' => null,
        ]);
});

test('inactive user cannot login', function () {
    // Arrange
    User::factory()->create([
        'email' => 'inactive@hrflow.test',
        'password' => 'password',
        'is_active' => false,
    ]);

    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'inactive@hrflow.test',
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Your account is inactive.',
            'data' => null,
        ]);
});
test('login requires email', function () {
    // Act
    $response =postJson('/api/v1/auth/login', [
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
        ]);
});
test('login rejects invalid email format', function () {
    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'not-an-email',
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
        ]);
});

test('login requires password', function () {
    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'admin@hrflow.test',
    ]);

    // Assert
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'password',
        ]);
});

test('login does not expose user password', function () {
    // Arrange
    User::factory()->create([
        'email' => 'admin@hrflow.test',
        'password' => 'password',
        'is_active' => true,
    ]);

    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'admin@hrflow.test',
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertSuccessful()
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonMissingPath('data.user.remember_token');
});


test('successful login creates an access token', function () {
    // Arrange
    $user = User::factory()->create([
        'email' => 'admin@hrflow.test',
        'password' => 'password',
        'is_active' => true,
    ]);

    // Act
    $response = postJson('/api/v1/auth/login', [
        'email' => 'admin@hrflow.test',
        'password' => 'password',
    ]);

    // Assert
    $response
        ->assertSuccessful()
        ->assertJsonPath('data.user.id', $user->id);

   assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
        'name' => 'api-token',
    ]);
});

});

describe('Me', function () {

test('authenticated user can get their profile', function () {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    $response =
        withToken($token)
        ->getJson('/api/v1/auth/me');

    $response
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email);
});

test('guest cannot get authenticated user profile', function () {
    $response = getJson('/api/v1/auth/me');

    $response
        ->assertUnauthorized();
});

});

describe('Logout', function () {

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $token = $user->createToken('api-token')->plainTextToken;

    $response = withToken($token)
        ->postJson('/api/v1/auth/logout');

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Logout successful.',
            'data' => null,
        ]);
});

test('guest cannot logout', function () {
    $response = postJson('/api/v1/auth/logout');

    $response
        ->assertUnauthorized();
});

});




describe('Change Password', function () {

test('authenticated user can change their password', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    $response = withToken($token)
        ->putJson('/api/v1/auth/password', [
            'current_password' => 'old-password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => null,
        ]);

    expect(
        password_verify('NewPassword123!', $user->refresh()->password)
    )->toBeTrue();
});
test('user cannot change password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    $response = withToken($token)
        ->putJson('/api/v1/auth/password', [
            'current_password' => 'wrong-password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'The current password is incorrect.',
            'data' => null,
        ]);
});

});

describe('Forgot Password', function () {


test('user can request a password reset link', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'admin@hrflow.test',
    ]);

    $response = postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => null,
        ]);
});

test('forgot password requires email', function () {
    $response = postJson('/api/v1/auth/forgot-password');

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
        ]);
});});


describe('Reset Password', function () {

test('user can reset their password with a valid token', function () {
    $user = User::factory()->create([
        'email' => 'admin@hrflow.test',
        'password' => 'old-password',
    ]);

    $token = Password::createToken($user);

    $response = postJson('/api/v1/auth/reset-password', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => null,
        ]);

    expect(
        password_verify('NewPassword123!', $user->refresh()->password)
    )->toBeTrue();
});

test('user cannot reset password with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'admin@hrflow.test',
    ]);

    $response = postJson('/api/v1/auth/reset-password', [
        'email' => $user->email,
        'token' => 'invalid-token',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'data' => null,
        ]);
});
});

});
