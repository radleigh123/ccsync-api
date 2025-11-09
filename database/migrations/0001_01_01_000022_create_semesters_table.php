<?php

use App\Enums\Status;
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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date_start');
            $table->date('date_end');
            $table->enum('status', Status::cases())->default(Status::OPEN);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE semesters ADD CONSTRAINT chk_date CHECK (date_start <= date_end)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
