<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['user_id', 'project_id', 'created_by', 'title', 'description', 'priority', 'status', 'start_date', 'due_date'];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function task()
    {
        return $this->belongsTo(Project::class);
    }
}
