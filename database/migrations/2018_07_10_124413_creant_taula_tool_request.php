<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreantTaulaToolRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tool_request', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('requester_id')->references('id')->on('employees');
            $table->unsignedInteger('requested_id')->references('id')->on('employees');
            $table->unsignedInteger('tool_id')->references('id')->on('tools');
            $table->unsignedInteger('company_id')->references('id')->on('companies');
            $table->unsignedInteger('state');
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
        Schema::dropIfExists('tool_request');
    }
}
