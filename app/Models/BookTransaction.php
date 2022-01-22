<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookTransaction extends Model
{
    use HasFactory;

    protected $table = 'rel_user_books';
	
    protected $fillable = [
        'user_id',
		'book_id',
        'type',
		'remark',
        'rent_date',
        'return_date'
    ];
}