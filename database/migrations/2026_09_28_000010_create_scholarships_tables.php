<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('provider');
            $table->date('application_start');
            $table->date('application_deadline')->index();
            $table->string('education_level', 30)->nullable()->index();
            $table->unsignedSmallInteger('target_grade_min')->nullable();
            $table->unsignedSmallInteger('target_grade_max')->nullable();
            $table->unsignedSmallInteger('available_slots')->nullable();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['status', 'application_deadline']);
        });

        Schema::create('scholarship_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('weight', 6, 2);
            $table->unsignedSmallInteger('maximum_score')->default(100);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('scholarship_eligibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('field', 40);
            $table->string('operator', 20);
            $table->string('value');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('scholarship_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('scholarship_committee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['scholarship_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_committee');
        Schema::dropIfExists('scholarship_documents');
        Schema::dropIfExists('scholarship_eligibility_rules');
        Schema::dropIfExists('scholarship_criteria');
        Schema::dropIfExists('scholarships');
    }
};
