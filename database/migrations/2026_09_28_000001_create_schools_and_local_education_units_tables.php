<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('school_code', 30)->nullable()->unique();
            $table->string('province', 60);
            $table->string('district', 60);
            $table->string('municipality', 60);
            $table->string('address')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();

            $table->index(['province', 'district']);
        });

        Schema::create('local_education_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('province', 60);
            $table->string('district', 60);
            $table->string('municipality', 60);
            $table->string('contact_information')->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();

            $table->index(['province', 'district']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_education_units');
        Schema::dropIfExists('schools');
    }
};
