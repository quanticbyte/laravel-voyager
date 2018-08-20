<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = 'tool_request';
    protected $fillable = ['id','requested_id','requester_id','tool_id','company_id','state'];
    protected $hidden = [];
}
