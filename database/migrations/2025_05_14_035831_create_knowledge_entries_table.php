<?php

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
        Schema::create('knowledge_entries', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('category')->default('general');
            $table->float('confidence')->default(1.0);
            $table->foreignId('question_id')->constrained('messages')->onDelete('cascade');
            $table->foreignId('answer_id')->nullable()->constrained('messages')->onDelete('set null');
            $table->string('media_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_entries');
    }
};
