<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserMemberSeeder extends Seeder
{
    public function run(): void
    {
        // best to flush this package's cache BEFORE seeding, to avoid cache conflict errors
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Members
            'view members',
            'add members',
            'edit members',
            'delete members',

            // Events
            'view events',
            'add events',
            'edit events',
            'delete events',

            // Officers
            'view officers',
            'add officers',
            'edit officers',
            'delete officers',

            // Semesters
            'view semesters',
            'add semesters',
            'edit semesters',
            'delete semesters',

            // Requirements
            'view requirements',
            'add requirements',
            'edit requirements',
            'delete requirements',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign created permissions

        // STUDENTS
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->syncPermissions([
            'view members',
            'view events',
            'view requirements',
        ]);

        // chaining

        $officerRole = Role::firstOrCreate(['name' => 'officer'])
            ->syncPermissions([
                'view members',
                'add members',
                'edit members',
                'delete members',
                'view events',
                'add events',
                'edit events',
                'delete events',
                'view requirements',
                'add requirements',
                'edit requirements',
                'delete requirements',
            ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions(Permission::all());

        $student = User::factory()->create([
            'first_name' => 'Student',
            'last_name' => 'Member',
            'email' => 'localstudent@student.com',
        ]);
        $student->assignRole($studentRole);
        Member::factory()->create([
            'user_id' => $student->id,
            'first_name' => 'Student',
            'last_name' => 'Member',
        ]);

        $officer = User::factory()->create([
            'first_name' => 'Officer',
            'last_name' => 'Member',
            'email' => 'localofficer@officer.com',
        ]);
        $officer->assignRole($officerRole);
        Member::factory()->create([
            'user_id' => $officer->id,
            'first_name' => 'Officer',
            'last_name' => 'Member',
        ]);

        $admin = User::factory()->create([
            'first_name' => 'Administrator',
            'last_name' => 'User',
            'email' => 'localadmin@admin.com',
        ]);
        $admin->assignRole($adminRole);
        Member::factory()->create([
            'user_id' => $admin->id,
            'first_name' => 'Administrator',
            'last_name' => 'User',
        ]);

        // --- Bulk random students ---
        User::factory(500)->create()->each(function ($user) use ($studentRole) {
            $user->assignRole($studentRole);

            Member::factory()->create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'id_school_number' => $user->id_school_number,
                'email' => $user->email,
            ]);
        });

        // --- Bulk random officers ---
        User::factory(30)->create()->each(function ($user) use ($officerRole) {
            $user->assignRole($officerRole);

            Member::factory()->create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'id_school_number' => $user->id_school_number,
                'email' => $user->email,
            ]);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
