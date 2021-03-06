<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSamplesTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('user_avatars', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('avatar');
            $table->timestamps();
        });


        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('name');
            $table->timestamps();
        });


        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cart_id');
            $table->string('name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('users')
            ->insert([
                'name' => 'John Doe'
            ]);


        \Illuminate\Support\Facades\DB::table('users')
            ->insert([
                'name'   => 'John Bolder',
                'status' => 'active'
            ]);

        \Illuminate\Support\Facades\DB::table('user_avatars')
            ->insert([
                'user_id' => 1,
                'avatar'  => 'users/default.png'
            ]);
    }

    public function down()
    {
        Schema::dropIfExists('samples');
    }

}
