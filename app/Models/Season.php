<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Season extends Model implements TranslatableContract
{
    use Translatable , HasFactory;
    protected $guarded;
    public $translatedAttributes = ['name' , 'description'];
}
