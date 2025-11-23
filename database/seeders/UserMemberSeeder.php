<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Semester;
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
            'promote members',

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

        $semester = Semester::orderByDesc('id')->first();

        // Create roles and assign created permissions

        // STUDENTS
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->syncPermissions([
            'view members',
            'view events',
            'view requirements',
        ]);

        $student = User::factory()->create([
            'display_name' => 'Student Student',
            'email' => 'localstudent@student.com',
        ]);
        $student->assignRole($studentRole);
        Member::factory()->create([
            'user_id' => $student,
            'first_name' => $student->display_name,
            'last_name' => 'Member',
            'semester_id' => $semester,
        ]);

        $roles = array('officer', 'secretary', 'treasurer', 'auditor', 'representative');
        for ($i = 0; $i < count($roles); $i++) {
            $role = $roles[$i];
            $officerRole = Role::firstOrCreate(['name' => $role])
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

            $officer = User::factory()->create([
                'display_name' => "$role.member",
                'email' => "local$role@officer.com",
            ]);
            $officer->assignRole($officerRole);
            Member::factory()->create([
                'user_id' => $officer,
                'first_name' => $officer->display_name,
                'last_name' => 'Member',
                'semester_id' => $semester,
            ]);
        }

        $presidentRole = Role::firstOrCreate(['name' => 'president'])
            ->syncPermissions(Permission::all());

        $admin = User::factory()->create([
            'display_name' => 'President',
            'email' => 'keaneradleigh@gmail.com',
            'password' => '123456',
            'firebase_uid' => 'NwVUqIRDldM0qa8mO6npUN1dy7r2',
        ]);
        $admin->assignRole($presidentRole);
        Member::factory()->create([
            'user_id' => $admin,
            'first_name' => $admin->display_name,
            'middle_name' => 'Konnichiwa',
            'last_name' => 'President',
            'enrollment_date' => date('2021-09-06'),
            'year' => 4,
            'is_paid' => true,
            'semester_id' => $semester,
        ]);

        $vicePresidentRole = Role::firstOrCreate(['name' => 'vice-president'])
            ->syncPermissions(Permission::all());

        $admin2 = User::factory()->create([
            'display_name' => 'Vice-President',
            'email' => 'vice-president@gmail.com',
        ]);
        $admin2->assignRole($vicePresidentRole);
        Member::factory()->create([
            'user_id' => $admin2,
            'first_name' => $admin2->display_name,
            'last_name' => 'Member',
            'semester_id' => $semester,
        ]);

        // --- Bulk random students ---
        User::factory(100)->create()->each(function ($user) use ($studentRole) {
            $user->assignRole($studentRole);

            $year = fake()->numberBetween(1, 4);
            $enrollmentDate = date("Y-m-d", strtotime("- {$year} years", strtotime("06 September")));

            Member::factory()->create([
                'user_id' => $user,
                'first_name' => $user->display_name,
                'enrollment_date' => $enrollmentDate,
                'year' => $year,
                'semester_id' => Semester::orderByDesc('id')->first(),
            ]);
        });

        /* User::factory()
            ->count(10)
            ->insert(); */

        // --- Bulk random officers ---
        User::factory(20)->create()->each(function ($user) use ($officerRole) {
            $user->assignRole($officerRole);

            $year = fake()->numberBetween(1, 4);
            $enrollmentDate = date("Y-m-d", strtotime("- {$year} years", strtotime("06 September")));

            Member::factory()->create([
                'user_id' => $user,
                'first_name' => $user->display_name,
                'enrollment_date' => $enrollmentDate,
                'year' => $year,
                'semester_id' => Semester::orderByDesc('id')->first(),
            ]);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
