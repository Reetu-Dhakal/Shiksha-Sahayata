<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterion_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('scholarship_criteria')->cascadeOnDelete();
            $table->foreignId('scored_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 7, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'criterion_id']);
        });

        Schema::create('selection_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('decision', 30);
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_decisions');
        Schema::dropIfExists('criterion_scores');
    }
};
