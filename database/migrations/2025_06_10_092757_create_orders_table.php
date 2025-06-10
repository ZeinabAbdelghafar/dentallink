<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            $table->decimal('total', 10, 2);
            $table->text('items')->nullable();

            // fawaterak
            $table->string('invoice_id')->nullable();
            $table->string('invoice_key')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable(); // Reference Number ----> webhook
            $table->boolean('paid')->default(false);
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
