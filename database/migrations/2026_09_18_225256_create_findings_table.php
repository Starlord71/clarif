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
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('rule_id');
            $table->string('file_path', 1024);
            $table->integer('line')->nullable();
            $table->string('severity');
            $table->text('message');
            $table->string('fingerprint', 64);
            $table->jsonb('payload');
            $table->timestamps();

            $table->index(['report_id', 'fingerprint']);
            $table->index('rule_id');
            $table->index('file_path');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
