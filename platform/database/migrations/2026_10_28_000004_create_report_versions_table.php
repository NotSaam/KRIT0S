<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->string('status', 20)->default('queued');
            $table->string('path')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->unsignedInteger('findings_count')->default(0);
            $table->timestamps();

            $table->unique(['engagement_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_versions');
    }
};
