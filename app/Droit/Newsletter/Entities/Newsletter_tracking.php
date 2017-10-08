<?php namespace App\Droit\Newsletter\Entities;

use Illuminate\Database\Eloquent\Model;

class Newsletter_tracking extends Model {

    protected $table = 'newsletter_tracking';
    protected $dates = ['time'];

	protected $fillable = ['event','time','MessageID','email','mj_campaign_id','mj_contact_id','customcampaign','mj_message_id','smtp_reply','CustomID','Payload'];

}