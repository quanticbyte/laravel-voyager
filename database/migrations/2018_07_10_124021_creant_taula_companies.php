<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreantTaulaCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('comercial_name',100);
            $table->string('real_name',100);
            $table->string('cif',9);
            $table->string('address',255);
            $table->string('contact_mail',100);
            $table->string('contact_name',100);
            $table->string('admin_name',50);
            $table->string('contact_tel',32);
            $table->string('logo',255)->nullable();
            $table->timestamp('data_alta')->useCurrent();
            $table->timestamp('data_baixa')->nullable();
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
        Schema::dropIfExists('companies');
    }
}
