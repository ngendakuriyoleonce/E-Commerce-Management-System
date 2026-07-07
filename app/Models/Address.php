<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'label', 'street', 'city', 'state', 'zip_code', 'country', 'is_primary',
    ];
}
