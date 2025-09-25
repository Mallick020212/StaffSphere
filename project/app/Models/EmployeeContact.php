<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class EmployeeContact extends Model
{
    protected $fillable = ['phone_number'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
