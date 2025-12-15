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
            'user_id'           => $student,
            'first_name'        => $student->display_name,
            'last_name'         => 'Member',
            'semester_id'       => $semester,
            'id_school_number'  => $student->id_school_number,
        ]);

        $student = User::factory()->create([
            'display_name'  => 'Keene Inting',
            'email'         => 'keane@gmail.com',
            'firebase_uid'  => 'IkfoLmmyrWfLQ3eKkYPjOVKXMdv2',
        ]);
        $student->assignRole($studentRole);
        Member::factory()->create([
            'user_id'           => $student,
            'first_name'        => $student->display_name,
            'last_name'         => 'Member',
            'semester_id'       => $semester,
            'id_school_number'  => $student->id_school_number,
        ]);

        $genOfficerRole = Role::firstOrCreate(['name' => 'officer']);

        $roles = array('secretary', 'treasurer', 'auditor', 'representative');

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
            $officer->assignRole([$studentRole, $genOfficerRole, $officerRole]);
            Member::factory()->create([
                'user_id'           => $officer,
                'first_name'        => $officer->display_name,
                'last_name'         => 'Member',
                'semester_id'       => $semester,
                'id_school_number'  => $officer->id_school_number,
            ]);
        }

        $presidentRole = Role::firstOrCreate(['name' => 'president'])
            ->syncPermissions(Permission::all());

        $admin = User::factory()->create([
            'display_name' => 'President',
            'email' => 'keaneradleigh@gmail.com',
            'password' => '123456',
            'firebase_uid' => 'Z5rc8fzElHbMGqQA3Nua3bfi23j1',
        ]);
        $admin->assignRole([$studentRole, $genOfficerRole, $presidentRole]);
        Member::factory()->create([
            'user_id'           => $admin,
            'first_name'        => $admin->display_name,
            'middle_name'       => 'Konnichiwa',
            'last_name'         => 'President',
            'enrollment_date'   => date('2021-09-06'),
            'year'              => 4,
            'is_paid'           => true,
            'semester_id'       => $semester,
            'id_school_number'  => $admin->id_school_number,
        ]);

        $vicePresidentRole = Role::firstOrCreate(['name' => 'vice-president'])
            ->syncPermissions(Permission::all());

        $admin2 = User::factory()->create([
            'display_name' => 'Vice-President',
            'email' => 'vice-president@gmail.com',
        ]);
        $admin2->assignRole([$studentRole, $genOfficerRole, $vicePresidentRole]);
        Member::factory()->create([
            'user_id'           => $admin2,
            'first_name'        => $admin2->display_name,
            'last_name'         => 'Member',
            'semester_id'       => $semester,
            'id_school_number'  => $admin2->id_school_number,
        ]);

        // --- Bulk unregistered members ---
        User::factory(50)->create();

        // --- Bulk random students ---
        User::factory(100)->create()->each(function ($user) use ($studentRole) {
            $user->assignRole($studentRole);

            $year = fake()->numberBetween(1, 4);
            $enrollmentDate = date("Y-m-d", strtotime("- {$year} years", strtotime("06 September")));

            $firstName = explode(".", $user->display_name)[0];
            $lastName = explode(".", $user->display_name)[1];

            Member::factory()->create([
                'user_id'           => $user,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'enrollment_date'   => $enrollmentDate,
                'year'              => $year,
                'semester_id'       => Semester::orderByDesc('id')->first(),
                'id_school_number'  => $user->id_school_number,
            ]);
        });

        /* User::factory()
            ->count(10)
            ->insert(); */

        // --- Bulk random officers ---
        User::factory(20)->create()->each(function ($user) use ($studentRole, $genOfficerRole, $officerRole) {
            $user->assignRole([$studentRole, $genOfficerRole, $officerRole]);

            $year = fake()->numberBetween(1, 4);
            $enrollmentDate = date("Y-m-d", strtotime("- {$year} years", strtotime("06 September")));

            $firstName = explode(".", $user->display_name)[0];
            $lastName = explode(".", $user->display_name)[1];

            Member::factory()->create([
                'user_id'           => $user,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'enrollment_date'   => $enrollmentDate,
                'year'              => $year,
                'semester_id'       => Semester::orderByDesc('id')->first(),
                'id_school_number'  => $user->id_school_number,
                'is_paid'           => true,
            ]);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
