<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
	//apuntamos a la tabla con al que trabajan
	protected $table = 'tools';
	
    protected $fillable = ['name', 'model', 'image_url','state','employee_id','company_id','is_active','serial_number'];
    protected $hidden = [];
}
