<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('type', 30);
            $table->string('status', 20)->default('planning')->index();
            $table->text('scope');
            $table->string('methodology')->nullable();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->text('executive_summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('engagement_user', function (Blueprint $table) {
            $table->foreignId('engagement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['engagement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_user');
        Schema::dropIfExists('engagements');
    }
};
