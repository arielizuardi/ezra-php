<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_menu');
    }
}
