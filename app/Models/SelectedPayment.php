<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectedPayment extends Model
{
    use SoftDeletes;

    protected $table = 'selected_payments';

    protected $fillable = [
        'calc_request_id',
        'month_count',
        'money_per_month'
    ];

    protected $dates = ['deleted_at'];

    public function calcRequest()
    {
        return $this->belongsTo('App\Models\CalcRequest', 'calc_request_id');
    }
}
