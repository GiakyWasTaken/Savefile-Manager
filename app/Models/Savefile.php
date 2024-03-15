<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Savefile extends Model
{
    use HasFactory;

    protected $table = 'savefile';

    protected $fillable = [
        'file_name',
        'file_path',
        'created_at',
        'updated_at',
        'fk_id_console',
    ];
}
