<?php namespace App\Droit\Newsletter\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Newsletter_campagnes extends Model {

	protected $fillable = ['sujet','auteurs','newsletter_id','status','send_at','api_campagne_id', 'hidden'];

    use SoftDeletes;

    protected $dates = ['deleted_at','send_at'];

    public function scopeNews($query,$newsletter_id)
    {
        if ($newsletter_id) $query->whereIn('newsletter_id',$newsletter_id);
    }

    public function newsletter(){

        return $this->belongsTo('App\Droit\Newsletter\Entities\Newsletter', 'newsletter_id', 'id');
    }

    public function content(){

        return $this->hasMany('App\Droit\Newsletter\Entities\Newsletter_contents', 'newsletter_campagne_id')->orderBy('rang');
    }

    public function tracking()
    {
        return $this->hasMany('App\Droit\Newsletter\Entities\Newsletter_tracking', 'CustomID');
    }
}