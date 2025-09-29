<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('venue');
            $table->date('event_date');
            $table->time('time_from');
            $table->time('time_to');
            $table->date('registration_start');
            $table->date('registration_end');
            $table->unsignedInteger('max_participants');
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('open');
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();

            // Ensure a member can only register once per event
            $table->unique(['event_id', 'member_id']);
        });

        // Add constraint to ensure registration dates make sense
        DB::statement('ALTER TABLE events ADD CONSTRAINT chk_registration_dates CHECK (registration_start <= registration_end)');
        DB::statement('ALTER TABLE events ADD CONSTRAINT chk_event_time CHECK (time_from < time_to)');
        DB::statement('ALTER TABLE events ADD CONSTRAINT chk_max_participants CHECK (max_participants > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
