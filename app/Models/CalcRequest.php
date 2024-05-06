<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalcRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phone_number', 'iin', 'name', 'surname', 'price', 'initial_payment',
        'additional_income', 'partner_income', 'children_count', 'ads_id', 'user_id',
    ];

    protected $dates = ['deleted_at'];

    public function calcRequest()
    {
        return $this->belongsTo('App\Models\CalcRequest', 'calc_request_id');
    }
}
