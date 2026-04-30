<?php

namespace Tests\Feature;

use App\User;
use App\Services\ChatbotGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutoLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'chatbot.validate_url' => 'https://bot.pta-papuabarat.go.id/api/magic-login/validate',
            'chatbot.internal_api_key' => 'testing-key',
            'chatbot.application_code' => 'wfh',
        ]);
    }

    public function testAutologinWithoutTokenFails()
    {
        $response = $this->get('/autologin');

        $response->assertStatus(401);
        $response->assertSee('Link login tidak valid atau sudah kedaluwarsa.');
        $this->assertGuest('web');
    }

    public function testAutologinWithInvalidGatewayResponseFails()
    {
        $this->mock(ChatbotGatewayService::class, function ($mock) {
            $mock->shouldReceive('validateMagicToken')
                ->once()
                ->with('invalid-token')
                ->andReturn(['valid' => false]);
        });

        $response = $this->get('/autologin?token=invalid-token');

        $response->assertStatus(401);
        $response->assertSee('Link login tidak valid atau sudah kedaluwarsa.');
        $this->assertGuest('web');
    }

    public function testAutologinWithValidGatewayResponseLogsInUser()
    {
        $user = $this->createUser(['app_user_id' => 'gateway-user-1']);

        $this->mock(ChatbotGatewayService::class, function ($mock) {
            $mock->shouldReceive('validateMagicToken')
                ->once()
                ->with('valid-token')
                ->andReturn([
                    'valid' => true,
                    'app_user_id' => 'gateway-user-1',
                ]);
        });

        $response = $this->get('/autologin?token=valid-token');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function testAutologinWithUnknownUserFails()
    {
        $this->mock(ChatbotGatewayService::class, function ($mock) {
            $mock->shouldReceive('validateMagicToken')
                ->once()
                ->with('valid-token')
                ->andReturn([
                    'valid' => true,
                    'app_user_id' => 'unknown-user',
                ]);
        });

        $response = $this->get('/autologin?token=valid-token');

        $response->assertStatus(401);
        $response->assertSee('Link login tidak valid atau sudah kedaluwarsa.');
        $this->assertGuest('web');
    }

    public function testAutologinWithInactiveUserFails()
    {
        $this->createUser([
            'app_user_id' => 'inactive-user',
            'is_active' => false,
        ]);

        $this->mock(ChatbotGatewayService::class, function ($mock) {
            $mock->shouldReceive('validateMagicToken')
                ->once()
                ->with('valid-token')
                ->andReturn([
                    'valid' => true,
                    'app_user_id' => 'inactive-user',
                ]);
        });

        $response = $this->get('/autologin?token=valid-token');

        $response->assertStatus(401);
        $response->assertSee('Link login tidak valid atau sudah kedaluwarsa.');
        $this->assertGuest('web');
    }

    private function createUser(array $attributes = [])
    {
        return User::create(array_merge([
            'app_user_id' => 'gateway-user',
            'name' => 'Pegawai Test',
            'nip' => '199001012020011001',
            'email' => 'pegawai@example.test',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'is_active' => true,
        ], $attributes));
    }
}
