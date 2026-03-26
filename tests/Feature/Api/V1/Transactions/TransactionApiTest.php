<?php

namespace Tests\Feature\Api\V1\Transactions;

use App\Models\Currency;
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

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'transactions.index', 'guard_name' => 'api']);
        Permission::create(['name' => 'transactions.store', 'guard_name' => 'api']);
        Permission::create(['name' => 'audit.index', 'guard_name' => 'api']);
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $admin->givePermissionTo(Permission::all());
        $this->user = User::factory()->create([
            'password' => Hash::make($this->password),
            'email'    => 'admin@cashela.com'
        ]);
        $this->user->assignRole('admin');
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function it_can_list_transactions(): void
    {
        Sanctum::actingAs($this->user);
        Transaction::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/transactions');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'message',
                'value' => [
                    'data' => [
                        '*' => [
                            'id',
                            'source_amount',
                            'target_amount',
                            'exchange_rate',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_create_a_transaction(): void
    {
        Sanctum::actingAs($this->user);
        $sourceCurrency = Currency::factory()->create(['code' => 'USD']);
        $targetCurrency = Currency::factory()->create(['code' => 'VES']);
        $payload = [
            'source_amount'      => 100.00,
            'source_currency_id' => $sourceCurrency->id,
            'target_currency_id' => $targetCurrency->id,
            'exchange_rate'      => 36.50,
        ];
        $response = $this->postJson('/api/v1/transactions', $payload);
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonFragment(['message' => __('transaction.store.success')]);
        $this->assertDatabaseHas('transactions', [
            'source_amount' => 100.00,
            'target_amount' => 3650.00, // 100 * 36.50
            'source_currency_id' => $sourceCurrency->id,
            'target_currency_id' => $targetCurrency->id
        ]);
    }

    #[Test]
    public function it_validates_required_fields_for_transaction(): void
    {
        Sanctum::actingAs($this->user);
        $response = $this->postJson('/api/v1/transactions', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function it_records_audit_when_transaction_is_created(): void
    {
        Sanctum::actingAs($this->user);
        $sourceCurrency = Currency::factory()->create();
        $targetCurrency = Currency::factory()->create();

        $payload = [
            'source_amount'      => 50.00,
            'source_currency_id' => $sourceCurrency->id,
            'target_currency_id' => $targetCurrency->id,
            'exchange_rate'      => 10.00,
        ];
        $this->postJson('/api/v1/transactions', $payload);
        $this->assertDatabaseHas('audits', [
            'event'          => 'created',
            'auditable_type' => 'App\Models\Transaction',
            'user_id'        => $this->user->id
        ]);
    }
}
