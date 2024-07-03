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
        Schema::table('winmax4_settings', function (Blueprint $table) {
            $table->bigInteger('type_docs_invoice')->nullable();
            $table->bigInteger('type_docs_invoice_receipt')->nullable();
            $table->bigInteger('type_docs_credit_note')->nullable();
            $table->bigInteger('type_docs_receipt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_settings', function (Blueprint $table) {
            $table->dropColumn('type_docs_invoice');
            $table->dropColumn('type_docs_invoice_receipt');
            $table->dropColumn('type_docs_credit_note');
            $table->dropColumn('type_docs_receipt');
        });
    }
};
