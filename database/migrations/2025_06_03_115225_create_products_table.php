<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->foreignId('category_id')->constrained();
            $table->json('images')->nullable();
            $table->integer('stock')->default(0);
            $table->json('ratings')->nullable()->comment('Stores rating counts (1-5 stars)');
            $table->float('average_rating')->default(0);
            $table->integer('total_ratings')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};