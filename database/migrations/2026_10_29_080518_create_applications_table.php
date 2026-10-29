<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company', 255);
            $table->string('role', 255);
            $table->string('url')->nullable();
            $table->string('location')->nullable();
            $table->string('salary')->nullable();
            $table->string('status')->default('wishlist');
            $table->unsignedInteger('position')->default(0);
            $table->date('applied_on')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
