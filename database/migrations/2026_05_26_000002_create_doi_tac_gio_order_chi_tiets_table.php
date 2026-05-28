<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('doi_tac_gio_order_chi_tiets')) {
            Schema::create('doi_tac_gio_order_chi_tiets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('doi_tac_gio_order_hang_id');
                $table->unsignedBigInteger('san_pham_id');
                $table->integer('so_luong')->default(1);
                $table->decimal('gia_order_tam_tinh', 15, 2)->default(0);
                $table->timestamps();

                $table->index('doi_tac_gio_order_hang_id', 'dt_gio_order_ct_gio_id_idx');
                $table->index('san_pham_id', 'dt_gio_order_ct_sp_id_idx');
                $table->unique(['doi_tac_gio_order_hang_id', 'san_pham_id'], 'dt_gio_order_ct_unique');
            });

            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM `doi_tac_gio_order_chi_tiets`'))
            ->pluck('Key_name')
            ->all();

        if (!in_array('dt_gio_order_ct_unique', $indexes, true)) {
            Schema::table('doi_tac_gio_order_chi_tiets', function (Blueprint $table) {
                $table->unique(['doi_tac_gio_order_hang_id', 'san_pham_id'], 'dt_gio_order_ct_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doi_tac_gio_order_chi_tiets');
    }
};
