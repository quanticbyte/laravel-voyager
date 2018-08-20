<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
	//apuntamos a la tabla con al que trabajan
	protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'Comercial_name', 'real_name','cif','address','contact_mail','contact_name','admin_name','contact_tel','logo'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
