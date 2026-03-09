<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMember extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'role_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}
