<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'description', 'slug'])]
class CurrentState extends Model
{
    /** @use HasFactory<\Database\Factories\CurrentStateFactory> */
    use HasFactory;
}
