<?php

use App\Enums\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FilesAndFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('folder_id')->nullable()->constrained();

            $table->string('name', 2000);
            $table->tinyInteger('read');
            $table->json('read_users')->nullable();
            $table->tinyInteger('write');
            $table->json('write_users')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('folder_id')->constrained();

            $table->string('name', 2000);
            $table->tinyInteger('read');
            $table->json('read_users')->nullable();
            $table->tinyInteger('write');
            $table->json('write_users')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
        Schema::dropIfExists('folders');
    }
}
