<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class EmployeeAddress extends Model
{
    protected $fillable = ['address_line','city','state','postal_code','country'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
