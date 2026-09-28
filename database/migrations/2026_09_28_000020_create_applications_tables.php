<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 40)->default('DRAFT')->index();
            $table->boolean('is_assisted')->default(false);
            $table->text('statement')->nullable();
            $table->string('previous_school', 150)->nullable();
            $table->unsignedTinyInteger('current_grade')->nullable();
            $table->decimal('grade_point_average', 4, 2)->nullable();
            $table->text('return_remarks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['scholarship_id', 'student_id']);
        });

        Schema::create('application_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('required_document_id')->nullable()->constrained('scholarship_documents')->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 60);
            $table->string('original_filename', 191);
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->string('status', 30)->default('UPLOADED');
            $table->timestamps();

            $table->unique(['application_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('applications');
    }
};
