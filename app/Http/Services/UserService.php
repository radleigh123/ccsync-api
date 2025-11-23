<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class UserService
{
    protected $auth;

    public function __construct()
    {
        $this->auth = Firebase::auth();
    }

    public function getAll()
    {
        return User::all();
    }

    public function create(array $data)
    {
        Log::info('Register method called', ['data' => $data]);
        DB::beginTransaction();
        $firebaseUser = null;

        try {
            // --- FIREBASE CREATE ---
            $firebaseUser = $this->auth->createUser([
                'displayName'   => $data['display_name'],
                'email'         => $data['email'],
                'password'      => $data['password'],
            ]);

            // --- DATABASE CREATE ---
            $user = User::create([
                'display_name'      => $data['display_name'],
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'firebase_uid'      => $firebaseUser->uid,
            ]);

            // --- ROLE ASSIGNMENT (DEFAULT:STUDENT) ---
            $user->assignRole('student');

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();

            if ($firebaseUser) {
                try {
                    $this->auth->deleteUser($firebaseUser->uid);
                } catch (Throwable $rollbackError) {
                    Log::error('Failed to delete Firebase user after rollback', [
                        'error' => $rollbackError->getMessage()
                    ]);
                }
            }

            throw new \Exception("Failed to create user: " . $e->getMessage());
        }
    }

    public function find(string $id)
    {
        return User::findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $user = User::findOrFail($id);
        $uid = $user->firebase_uid;

        DB::beginTransaction();

        try {
            // --- DATABASE UPDATE ---
            $user->update($data);

            // --- FIREBASE UPDATE ---
            if (array_key_exists('email', $data)) {
                $this->auth->changeUserEmail($uid, $data['email']);
            }

            if (array_key_exists('display_name', $data)) {
                $this->auth->updateUser($uid, [
                    'displayName'   => $data['display_name']
                ]);
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            try {
                if (array_key_exists('email', $data)) {
                    $this->auth->changeUserEmail($uid, $user->getOriginal('email'));
                }

                if (array_key_exists('display_name', $data)) {
                    $this->auth->updateUser($uid, [
                        'displayName'   => $user->getOriginal('display_name')
                    ]);
                }
            } catch (Throwable $rollbackError) {
                Log::error('Failed to update Firebase user after rollback', [
                    'error' => $rollbackError->getMessage()
                ]);
            }
            throw new \Exception("Failed to update user: " . $e->getMessage());
        }
    }

    public function delete(string $id)
    {
        $user = User::findOrFail($id);
        $origEmail = $user->email;
        $origDisplayName = $user->display_name;
        $origPassword = $user->password;

        DB::beginTransaction();

        try {
            // --- DATABASE DELETE ---
            $user->delete();

            // --- FIREBASE DELETE ---
            $this->auth->deleteUser($user->firebase_uid);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            try {
                $this->auth->createUser([
                    'displayName'   => $origDisplayName,
                    'email'         => $origEmail,
                    'password'      => $origPassword,
                ]);
            } catch (Throwable $rollbackError) {
                Log::error('Failed to delete Firebase user after rollback', [
                    'error' => $rollbackError->getMessage()
                ]);
            }
            throw new \Exception("Failed to delete user: " . $e->getMessage());
        }
    }

    public function login(array $data)
    {
        try {
            $signInResult = $this->auth->signInWithEmailAndPassword(
                $data['email'],
                $data['password'],
            );
            $firebaseUser = $signInResult->data();
            $uid = $firebaseUser['localId'] ?? null;

            if (!$uid) {
                throw new \Exception("Failed to retrieve Firebase UID");
            }

            $user = User::where('firebase_uid', $uid)->first();

            if (!$user) {
                throw new \Exception("User does not exist");
            }

            return [
                'user'          => $user,
                'firebaseUser'  => $firebaseUser,
            ];
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($message == "INVALID_LOGIN_CREDENTIALS")
                throw new \Exception("Incorrect email or password.", 422);
            else
                throw new \Exception("Failed to login user: " . $e->getMessage());
        }
    }

    public function updatePassword(string $id, array $data)
    {
        $user = User::findOrFail($id);
        $currentHashedPass = $user->password;
        $currentCleanPass = $data['current_password'];

        if (!Hash::check($currentCleanPass, $currentHashedPass)) {
            throw new \Exception("Current password is not correct.", 401);
        }

        DB::beginTransaction();

        try {
            $hashedPassword = Hash::make($data['password']);
            $user->password = $hashedPassword;
            $user->save();

            $this->auth->changeUserPassword($user->firebase_uid, $data['password']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            try {
                $this->auth->changeUserPassword($user->firebase_uid, $currentCleanPass);
            } catch (Throwable $rollbackError) {
                Log::error('Failed to update Firebase password after rollback', [
                    'error' => $rollbackError->getMessage()
                ]);
            }
            throw new \Exception("Failed to update password: " . $e->getMessage(), 500);
        }
    }

    public function resetPasswordLink(array $data)
    {
        try {
            $this->auth->sendPasswordResetLink($data['email']);
        } catch (\Exception $e) {
            throw new \Exception("Failed to send reset password link: " . $e->getMessage(), 500);
        }
    }

    public function emailVerificationLink(array $data)
    {
        try {
            $this->auth->sendEmailVerificationLink($data['email']);
        } catch (\Exception $e) {
            throw new \Exception("Failed to send email verification link: " . $e->getMessage(), 500);
        }
    }

    public function updateEmailVerification(string $email)
    {
        $user = User::where('email', $email)->firstOrFail();
        $user->email_verified = true;
        $user->save();
        return $user;
    }
}
