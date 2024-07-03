<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalcRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phone_number', 'iin', 'name', 'surname','patronymic', 'price', 'initial_payment',
        'additional_income', 'partner_income', 'children_count', 'ads_id', 'user_id',
    ];

    protected $dates = ['deleted_at'];

    public function selectedPayments()
    {
        return $this->hasMany('App\Models\SelectedPayment', 'calc_request_id');
    }
}
