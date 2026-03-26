<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\CurrencyFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedUsers();
        $this->seedCurrency();
    }

    private function seedRolesAndPermissions(): void
    {
        $guards = ['api', 'web'];
        $permissions = [
            'transactions.index',
            'transactions.show',
            'transactions.update',
            'transactions.store',
            'audit.index',
            'currency.index',
            'currency.check',
        ];

        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard
                ]);
            }

            // Crear Rol Admin y sincronizar
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $adminRole->syncPermissions(Permission::where('guard_name', $guard)->get());

            // Crear Rol User y sincronizar
            $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
            $userRole->syncPermissions([
                'transactions.index',
                'transactions.show',
                'transactions.store',
                'audit.index',
                'currency.index',
                'currency.check',
            ]);
        }
    }

    private function seedUsers(): void
    {
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@cashela.com'],
            [
                'name'              => 'Admin Cashela',
                'email_verified_at' => now(),
                'password'          => Hash::make('1234567890'),
            ]
        );
        $adminUser->assignRole(Role::where('name', 'admin')->where('guard_name', 'web')->first());
        $adminUser->assignRole(Role::where('name', 'admin')->where('guard_name', 'api')->first());

        $yonathan = User::updateOrCreate(
            ['email' => 'yonathan@cashela.com'],
            [
                'name'              => 'Yonathan R.',
                'email_verified_at' => now(),
                'password'          => Hash::make('1234567890'),
            ]
        );

        $yonathan->syncRoles([
            Role::where('name', 'user')->where('guard_name', 'web')->first(),
            Role::where('name', 'user')->where('guard_name', 'api')->first(),
        ]);

        // 3. Usuarios adicionales
        User::factory()->count(5)->create()->each(function ($user) {
            $user->assignRole(Role::where('name', 'user')->where('guard_name', 'web')->first());
            $user->assignRole(Role::where('name', 'user')->where('guard_name', 'api')->first());
        });
    }

    private function seedCurrency()
    {
        return CurrencyFactory::createFullSet();
    }
}
