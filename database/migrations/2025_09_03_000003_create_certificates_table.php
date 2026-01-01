<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->string('transaction_hash')->nullable();
            $table->string('on_chain_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};





