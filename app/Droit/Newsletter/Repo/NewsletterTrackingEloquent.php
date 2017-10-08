<?php namespace App\Droit\Newsletter\Repo;

use App\Droit\Newsletter\Entities\Newsletter_tracking as M;
use App\Droit\Newsletter\Repo\NewsletterTrackingInterface;

class NewsletterTrackingEloquent implements NewsletterTrackingInterface{

	protected $tracking;

	public function __construct(M $tracking)
	{
		$this->tracking = $tracking;
	}
	
	public function getAll(){
		
		return $this->tracking->all();
	}
	
	public function find($id){

		return $this->tracking->where('CustomID','=',$id)->get();
	}

    public function create(array $data){

        $tracking = $this->tracking->create(array(
            'event'          => isset($data['event']) ? $data['event'] : null,
            'time'           => \Carbon\Carbon::createFromTimestamp($data['time'])->toDateTimeString(),
            'MessageID'      => isset($data['MessageID']) ? $data['MessageID'] : null,
            'email'          => isset($data['email']) ? $data['email'] : null,
            'mj_campaign_id' => isset($data['mj_campaign_id']) ? $data['mj_campaign_id'] : null,
            'mj_contact_id'  => isset($data['mj_contact_id']) ? $data['mj_contact_id'] : null,
            'customcampaign' => isset($data['customcampaign']) ? $data['customcampaign'] : null,
            'mj_message_id'  => isset($data['mj_message_id']) ? $data['mj_message_id'] : null,
            'smtp_reply'     => isset($data['smtp_reply']) ? $data['smtp_reply'] : null,
            'CustomID'       => isset($data['CustomID']) ? $data['CustomID'] : null,
            'Payload'        => isset($data['Payload']) ? $data['Payload'] : null,
        ));

        if( ! $tracking )
        {
            return false;
        }

        return $tracking;
    }

    public function update(array $data){

        $tracking = $this->tracking->findOrFail($data['id']);

        if( !$tracking )
        {
            return false;
        }

        $tracking->fill($data);
        $tracking->save();

        return $tracking;
    }

	public function delete($id){

        $tracking = $this->tracking->find($id);

        return $tracking->delete();
    }
}
