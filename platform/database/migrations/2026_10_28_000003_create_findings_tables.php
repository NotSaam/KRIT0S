<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finding_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('cvss_vector');
            $table->text('description');
            $table->text('impact');
            $table->text('remediation');
            $table->text('references')->nullable();
            $table->timestamps();
        });

        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('finding_templates')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('number');
            $table->string('title');
            $table->string('cvss_vector');
            $table->decimal('cvss_score', 3, 1);
            $table->string('severity', 20)->index();
            $table->string('status', 20)->default('open')->index();
            $table->text('affected_assets');
            $table->text('description');
            $table->text('proof_of_concept')->nullable();
            $table->text('impact');
            $table->text('remediation');
            $table->text('references')->nullable();
            $table->timestamps();

            $table->unique(['engagement_id', 'number']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40)->index();
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('findings');
        Schema::dropIfExists('finding_templates');
    }
};
