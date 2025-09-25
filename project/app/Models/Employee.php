<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Employee extends Model
{
    protected $fillable = ['first_name','last_name','email','department_id'];

    public function contacts() {
        return $this->hasMany(EmployeeContact::class);
    }

    public function addresses() {
        return $this->hasMany(EmployeeAddress::class);
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }
}
