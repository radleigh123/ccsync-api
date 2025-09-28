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
        Schema::create('programs', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('suffix', 50)->nullable();
            $table->unsignedInteger('id_school_number')->unique();
            $table->string('email')->unique()->nullable();
            $table->date('birth_date');
            $table->date('enrollment_date');
            $table->string('program');
            $table->foreign('program')->references('code')->on('programs');
            $table->unsignedTinyInteger('year')->default(1);
            $table->boolean('is_paid')->default(false);
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
        Schema::dropIfExists('programs');
    }
};
