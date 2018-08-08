<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreantTaulaTools extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',100);
            $table->string('model',100);
            $table->string('image_url',255);
            $table->unsignedInteger('state')->default(1);
            $table->unsignedInteger('employee_id')->references('id')->on('employees');
            $table->unsignedInteger('company_id')->regerences('id')->on('companies');
            $table->boolean('is_active')->default(1);
            $table->string('serial_number',100);
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
        Schema::dropIfExists('tools');
    }
}
