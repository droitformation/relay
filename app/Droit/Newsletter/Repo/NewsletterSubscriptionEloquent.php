<?php namespace App\Droit\Newsletter\Repo;

use App\Droit\Newsletter\Entities\Newsletter_subscriptions as M;
use App\Droit\Newsletter\Repo\NewsletterSubscriptionInterface;

class NewsletterSubscriptionEloquent implements NewsletterSubscriptionInterface{

	protected $subscribe;

	/**
	 * Construct a new SentryUser Object
	 */
	public function __construct(M $subscribe)
	{
		$this->subscribe = $subscribe;
	}
	
	public function getAll($newsletter_id){
		
		return $this->subscribe->where('newsletter_id','=',$newsletter_id)->get();
	}

	public function find($id){
				
		return $this->subscribe->findOrFail($id);
	}

    public function delete($id){

        $subscribe = $this->subscribe->find($id);

        return $subscribe->delete();
    }
}
