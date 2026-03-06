<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    protected $fillable = ['title'];
    use HasFactory;
    use SoftDeletes;
    
    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class);
    }
}
