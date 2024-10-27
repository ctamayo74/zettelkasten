<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zettel extends Model
{
    //
    protected $fillable = [
        'title',
        'body',
        'reference',
    ];
}
