<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{
    protected $fillable = [
        'billing_id', 'item_name', 'item_type', 'unit_price', 'quantity'
    ];
}