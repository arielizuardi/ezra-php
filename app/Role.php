<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';

    public function menus()
    {
        return $this->belongsToMany('App\Menu', 'role_menu');
    }
}
