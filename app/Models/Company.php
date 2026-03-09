<?php
namespace App\Models;

use App\Models\CompanyMember;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function members()
    {
        return $this->hasMany(CompanyMember::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_members')
            ->withPivot('role_id')
            ->withTimestamps();
    }

}
