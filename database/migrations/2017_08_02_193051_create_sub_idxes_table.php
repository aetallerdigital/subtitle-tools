<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubIdxesTable extends Migration
{
    public function up()
    {
        Schema::create('sub_idxes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string("page_id")->unique();
            $table->string("store_directory");
            $table->string("filename");
            $table->string("original_name");
            $table->string("sub_hash");
            $table->string("idx_hash");
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_idxes');
    }

}
