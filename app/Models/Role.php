<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{

    use HasFactory;
    use SoftDeletes;

    const OWNER   = 'Owner';
    const ADMIN   = 'Admin';
    const MANAGER = 'Manager';
    const MEMBER  = 'Member';

    protected $fillable = ['title'];

    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
}
