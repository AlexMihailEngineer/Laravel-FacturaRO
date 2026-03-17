<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status')->default('pending'); // pending, processing, completed, failed

            // Metadata
            $table->string('series');
            $table->string('number');
            $table->date('issue_date');

            // Supplier (Furnizor)
            $table->string('supplier_name');
            $table->string('supplier_cui');
            $table->string('supplier_registration_number'); // J40/...
            $table->text('supplier_address');

            // Customer (Client)
            $table->string('customer_name');
            $table->string('customer_cui');
            $table->string('customer_registration_number')->nullable(); // Optional for PF
            $table->text('customer_address');

            // Storage & Totals (for quick lookup)
            $table->decimal('total_net', 15, 4);
            $table->decimal('total_vat', 15, 4);
            $table->decimal('total_gross', 15, 4);
            $table->string('file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_requests');
    }
};
