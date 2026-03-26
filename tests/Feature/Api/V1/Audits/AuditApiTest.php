<?php

namespace Tests\Feature\Api\V1\Audits;

use App\Models\Audit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuditApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'audit.index', 'guard_name' => 'api']);

        $admin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $admin->givePermissionTo('audit.index');
        $this->user = User::factory()->create([
            'password' => Hash::make($this->password),
            'email'    => 'admin@cashela.com'
        ]);
        $this->user->assignRole('admin');
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    #[Test]
    public function it_can_list_audits_for_a_transaction(): void
    {
        Sanctum::actingAs($this->user);
        $transaction = Transaction::factory()->create();
        Audit::create([
            'event'          => 'created',
            'auditable_id'   => $transaction->id,
            'auditable_type' => Transaction::class,
            'user_id'        => $this->user->id,
            'old_values'     => [],
            'new_values'     => [
                'source_amount' => $transaction->source_amount,
                'exchange_rate' => $transaction->exchange_rate
            ],
            'url'            => 'http://localhost/api/v1/transactions',
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'TestingAgent'
        ]);

        $response = $this->getJson("/api/v1/transactions/{$transaction->id}/audits");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'message',
                'value' => [
                    '*' => [
                        'id',
                        'event',
                        'auditable_type',
                        'old_values',
                        'new_values',
                        'user_id',
                        'created_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_returns_forbidden_if_user_has_no_audit_permission(): void
    {
        $commonUser = User::factory()->create();
        Sanctum::actingAs($commonUser);
        $transaction = Transaction::factory()->create();
        $response = $this->getJson("/api/v1/transactions/{$transaction->id}/audits");
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
