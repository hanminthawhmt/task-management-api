<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{

    use HasFactory;
    use SoftDeletes;

    const OWNER   = 'owner';
    const ADMIN   = 'admin';
    const MANAGER = 'manager';
    const MEMBER  = 'member';

    protected $fillable = ['title'];

    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class);
    }
}
