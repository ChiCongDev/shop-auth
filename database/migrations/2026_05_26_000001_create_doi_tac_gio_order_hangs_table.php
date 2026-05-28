<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doi_tac_gio_order_hangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doi_tac_id')->unique();
            $table->timestamps();

            $table->index('doi_tac_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doi_tac_gio_order_hangs');
    }
};
