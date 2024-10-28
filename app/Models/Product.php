<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'cost_price', 'quantity'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
