<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            // $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Backfill usernames from email prefix if null/empty (PostgreSQL)
        DB::statement("
            UPDATE users
            SET username = split_part(email, '@', 1)
            WHERE (username IS NULL OR username = '')
              AND email IS NOT NULL AND email <> ''
        ");

        // Ensure all usernames are unique by appending a suffix where needed
        // (simple approach to avoid collisions)
        DB::statement("
            WITH dups AS (
                SELECT id, username,
                       row_number() OVER (PARTITION BY username ORDER BY id) AS rn
                FROM users
                WHERE username IS NOT NULL AND username <> ''
            )
            UPDATE users u
            SET username = u.username || '_' || d.rn
            FROM dups d
            WHERE u.id = d.id AND d.rn > 1
        ");

        // Enforce NOT NULL and UNIQUE (PostgreSQL)
        DB::statement("ALTER TABLE users ALTER COLUMN username SET NOT NULL");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS users_username_unique ON users (username)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique index; keep data intact
        DB::statement("DROP INDEX IF EXISTS users_username_unique");
        // Allow nulls again
        DB::statement("ALTER TABLE users ALTER COLUMN username DROP NOT NULL");

        Schema::dropIfExists('users');
    }
};
