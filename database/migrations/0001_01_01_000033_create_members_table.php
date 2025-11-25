<?php

use App\Enums\Gender;
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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix', 50)->nullable();
            $table->unsignedInteger('id_school_number')->unique();
            $table->date('birth_date');
            $table->date('enrollment_date');

            // since there is no "id" column on programs table
            $table->string('program');
            $table->foreign('program')->references('code')->on('programs');

            $table->unsignedTinyInteger('year')->default(1);
            $table->boolean('is_paid')->default(false);
            $table->enum('gender', Gender::cases())->default(Gender::OTHER);
            $table->text('biography')->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->foreignId('semester_id')->nullable()
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE members ADD CONSTRAINT chk_year CHECK (year BETWEEN 1 AND 4)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
