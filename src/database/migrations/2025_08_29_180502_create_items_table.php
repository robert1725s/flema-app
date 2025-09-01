<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('image_path');
            $table->string('brand')->nullable();
            $table->unsignedInteger('price');
            $table->tinyInteger('condition');
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('puchaser_id')->constrained('users')->nullable();
            $table->string('post_code')->nullable();
            $table->string('address')->nullable();
            $table->string('building')->nullable();
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
        Schema::dropIfExists('items');
    }
}
