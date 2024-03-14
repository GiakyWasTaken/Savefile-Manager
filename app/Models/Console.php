<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Console extends Model
{
    use HasFactory;

    protected $table = 'console';

    protected $fillable = [
        'console_name',
        'created_at',
        'updated_at',
    ];
}
