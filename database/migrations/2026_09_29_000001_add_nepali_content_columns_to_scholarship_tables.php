<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table): void {
            $table->string('title_np')->nullable()->after('title');
            $table->text('description_np')->nullable()->after('description');
            $table->string('provider_np')->nullable()->after('provider');
        });

        Schema::table('scholarship_criteria', function (Blueprint $table): void {
            $table->string('name_np')->nullable()->after('name');
            $table->string('description_np')->nullable()->after('description');
        });

        Schema::table('scholarship_eligibility_rules', function (Blueprint $table): void {
            $table->string('description_np')->nullable()->after('description');
        });

        Schema::table('scholarship_documents', function (Blueprint $table): void {
            $table->string('description_np')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table): void {
            $table->dropColumn(['title_np', 'description_np', 'provider_np']);
        });

        Schema::table('scholarship_criteria', function (Blueprint $table): void {
            $table->dropColumn(['name_np', 'description_np']);
        });

        Schema::table('scholarship_eligibility_rules', function (Blueprint $table): void {
            $table->dropColumn(['description_np']);
        });

        Schema::table('scholarship_documents', function (Blueprint $table): void {
            $table->dropColumn(['description_np']);
        });
    }
};
