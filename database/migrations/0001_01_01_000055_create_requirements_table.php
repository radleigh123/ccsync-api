<?php

use App\Enums\Gender;
use App\Enums\RequirementStatus;
use App\Enums\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /** template */
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', Type::cases())->default(Type::DOCUMENT); //TODO: need its final values
            $table->boolean('is_active')->default(true);
            $table->foreignId('semester_id')->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamps();
        });

        /** Requirement made active for a specific semester, one offering per requirement per semester */
        Schema::create('offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()
                ->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()
                ->nullOnDelete();
            $table->date('open_at')->nullable();
            $table->date('close_at')->nullable();
            $table->integer('max_submissions')->default(1); // allow re-submission attempts
            $table->boolean('active')->default(true);
            $table->timestamps();

            // TODO: expound on this
            $table->index('requirement_id');
            $table->index('semester_id');
            // Ensure one offering per semester
            $table->unique(['requirement_id', 'semester_id']);
        });

        /** Member's submission for a specific offering */
        Schema::create('compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offering_id')->constrained()
                ->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()
                ->cascadeOnDelete();
            $table->enum('status', RequirementStatus::cases())->default(RequirementStatus::PENDING);
            $table->integer('attempt')->default(1);
            $table->timestamp('submitted_at')->nullable()->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('members')
                ->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('offering_id');
            $table->index('member_id');
            // Only one user, one active attempt unless allowed
            $table->unique(['offering_id', 'member_id', 'attempt']);
        });

        /** Uploaded files for compliance */
        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_id')->constrained()
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('members')
                ->nullOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index('compliance_id');
        });

        /** Audit log for status changes */
        Schema::create('compliance_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_id')->constrained()
                ->cascadeOnDelete();
            $table->enum('old_status', RequirementStatus::cases())->nullable();
            $table->enum('new_status', RequirementStatus::cases())->default(RequirementStatus::PENDING);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->foreign('changed_by')->references('id')->on('members')
                ->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('compliance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirements');
        Schema::dropIfExists('offerings');
        Schema::dropIfExists('compliances');
        Schema::dropIfExists('compliance_documents');
        Schema::dropIfExists('compliance_audits');
    }
};
