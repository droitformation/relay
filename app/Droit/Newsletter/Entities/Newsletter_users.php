<?php namespace App\Droit\Newsletter\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Newsletter_users extends Model {

    use SoftDeletes;

    protected $dates    = ['activated_at','deleted_at'];
	protected $fillable = ['email','activation_token','activated_at'];

    public function getActivatedAttribute()
    {
        return $this->activated_at && $this->activated_at->timestamp < 0 ? null : $this->activated_at;
    }

    public function subscriptions()
    {
        return $this->belongsToMany('App\Droit\Newsletter\Entities\Newsletter', 'newsletter_subscriptions', 'user_id', 'newsletter_id');
    }
}