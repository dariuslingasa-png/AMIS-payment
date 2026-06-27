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
        Schema::table('student_account_payments', function (Blueprint $table) {
            $table->string('ocr_status', 50)->default('skipped')->after('receipt_url'); // matched, mismatch, failed, skipped
            $table->text('ocr_raw_text')->nullable()->after('ocr_status');
            $table->string('ocr_scanned_ref', 100)->nullable()->after('ocr_raw_text');
            $table->decimal('ocr_scanned_amount', 10, 2)->nullable()->after('ocr_scanned_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_account_payments', function (Blueprint $table) {
            $table->dropColumn(['ocr_status', 'ocr_raw_text', 'ocr_scanned_ref', 'ocr_scanned_amount']);
        });
    }
};
