<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Support\Str;

class AuditTrailTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create a test scenario
        $user = User::first();

        if (!$user) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        $this->command->info('Creating 5 test audit trail records...');

        // 1. Login Activity
        AuditTrail::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'activity_type' => 'login',
            'model_type' => 'App\Models\User',
            'model_id' => $user->id,
            'route' => 'login',
            'method' => 'POST',
            'status_code' => 200,
            'session_id' => Str::random(40),
            'request_id' => Str::uuid(),
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'before_data' => null,
            'after_data' => [
                'username' => $user->username,
                'name' => $user->name,
                'login_time' => now()->toDateTimeString()
            ],
            'logged_at' => now()->subHours(2),
        ]);

        // 2. Create Activity (New Company)
        AuditTrail::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'activity_type' => 'create',
            'model_type' => 'App\Models\Company',
            'model_id' => 999,
            'route' => 'companies.store',
            'method' => 'POST',
            'status_code' => 201,
            'session_id' => Str::random(40),
            'request_id' => Str::uuid(),
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'before_data' => null,
            'after_data' => [
                'company_code' => 'TEST01',
                'nama' => 'Test Company One',
                'shortname' => 'TC1',
                'deskripsi' => 'This is a test company created for audit trail demonstration',
                'created_by' => $user->name
            ],
            'logged_at' => now()->subHours(1)->subMinutes(45),
        ]);

        // 3. Update Activity (Modified Job Role)
        AuditTrail::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'activity_type' => 'update',
            'model_type' => 'App\Models\JobRole',
            'model_id' => 123,
            'route' => 'job-roles.update',
            'method' => 'PUT',
            'status_code' => 200,
            'session_id' => Str::random(40),
            'request_id' => Str::uuid(),
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'before_data' => [
                'job_role_id' => 'JR001',
                'nama' => 'Senior Manager',
                'deskripsi' => 'Senior management position',
                'status' => 'Active',
                'updated_by' => 'Admin'
            ],
            'after_data' => [
                'job_role_id' => 'JR001',
                'nama' => 'Senior Manager - Updated',
                'deskripsi' => 'Senior management position with expanded responsibilities',
                'status' => 'Active',
                'updated_by' => $user->name
            ],
            'logged_at' => now()->subMinutes(30),
        ]);

        // 4. Delete Activity (Removed Tcode)
        AuditTrail::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'activity_type' => 'delete',
            'model_type' => 'App\Models\Tcode',
            'model_id' => 456,
            'route' => 'tcodes.destroy',
            'method' => 'DELETE',
            'status_code' => 200,
            'session_id' => Str::random(40),
            'request_id' => Str::uuid(),
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'before_data' => [
                'code' => 'SU01',
                'sap_module' => 'Basis',
                'deskripsi' => 'User Maintenance',
                'source' => 'upload'
            ],
            'after_data' => null,
            'logged_at' => now()->subMinutes(15),
        ]);

        // 5. Password Change Activity
        AuditTrail::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'activity_type' => 'password_change',
            'model_type' => 'App\Models\User',
            'model_id' => $user->id,
            'route' => 'profile.update-password',
            'method' => 'POST',
            'status_code' => 200,
            'session_id' => Str::random(40),
            'request_id' => Str::uuid(),
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'before_data' => null,
            'after_data' => [
                'message' => 'Password updated',
                'changed_at' => now()->toDateTimeString()
            ],
            'logged_at' => now()->subMinutes(5),
        ]);

        $this->command->info('✅ Successfully created 5 test audit trail records!');
        $this->command->info('Visit /audit-trails to view them.');
    }
}
