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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leader_id')->constrained('leaders')->onDelete('cascade');
            $table->string('chapter_name'); // Chapter Name
            $table->string('image')->nullable(); 
            $table->string('contact_email')->nullable(); 
            $table->string('contact_phone')->nullable(); 
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('address')->nullable();
            $table->longText('intro')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Performance Indexes
            $table->index(['name']);
            $table->index(['city']);
            $table->index(['state_province']);
            $table->index(['is_active']);
            $table->index(['created_at']);
            
            // Composite index for location-based queries
            $table->index(['city', 'state_province']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
