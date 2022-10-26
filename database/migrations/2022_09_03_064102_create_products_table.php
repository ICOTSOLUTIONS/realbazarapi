<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('title')->nullable();
            $table->string('price')->nullable();
            $table->string('discount_price')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->mediumText('desc')->nullable();
            // $table->string('brand')->nullable();
            // $table->string('selected_qty')->nullable();
            // $table->string('type')->nullable();
            $table->string('variations')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->string('tags')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
