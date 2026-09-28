<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('relationship', 50);
            $table->string('phone', 20);
            $table->string('citizenship_number', 40)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('scholar_student_id', 30)->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('guardian_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('birth_registration_number', 50)->nullable()->index();
            $table->string('name', 150);
            $table->string('name_np', 150)->nullable();
            $table->date('date_of_birth');
            $table->string('gender', 20);
            $table->string('education_level', 30)->nullable()->index();
            $table->unsignedSmallInteger('grade')->index();
            $table->string('province', 60)->nullable();
            $table->string('district', 60)->nullable();
            $table->string('municipality', 60)->nullable();
            $table->string('student_category', 40)->nullable()->index();
            $table->string('verification_status', 20)->default('UNVERIFIED')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('guardians');
    }
};
