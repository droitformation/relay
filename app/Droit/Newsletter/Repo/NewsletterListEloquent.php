<?php namespace App\Droit\Newsletter\Repo;

use App\Droit\Newsletter\Entities\Newsletter_lists as M;
use App\Droit\Newsletter\Repo\NewsletterListInterface;

class NewsletterListEloquent implements NewsletterListInterface{

	protected $list;

	public function __construct(M $list)
	{
		$this->list = $list;
	}
	
	public function getAll(){
		
		return $this->list->all();
	}

    public function getForColloques(){
        return $this->list->whereNotNull('colloque_id')->get();
    }
	
	public function find($id){

		return $this->list->with(['emails'])->find($id);
	}

    public function findByColloque($colloque_id)
    {
	    return $this->list->where('colloque_id','=',$colloque_id)->firstOrFail();
    }

    public function emailExist($id,$email)
    {
        return $this->list->where('id','=',$id)->whereHas('emails', function($q) use ($email) {
            $q->where('email','=', $email);
        })->get();
    }
    
    public function create(array $data){

        $list = $this->list->create(array(
            'title'        => $data['title'],
            'colloque_id'  => isset($data['colloque_id']) ? $data['colloque_id'] : null,
            'created_at'   => date('Y-m-d G:i:s'),
            'updated_at'   => date('Y-m-d G:i:s')
        ));

        if( ! $list ) {
            return false;
        }

        if(isset($data['emails']) && !empty($data['emails'])) {
            foreach($data['emails'] as $email) {
                $list->emails()->save(new \App\Droit\Newsletter\Entities\Newsletter_emails(['list_id' => $list->id, 'email' => $email]));
            }
        }

        if(isset($data['specialisations']) && !empty($data['specialisations'])){
            $list->specialisations()->attach($data['specialisations']);
        }

        return $list;
    }

    public function update(array $data){

        $list = $this->list->findOrFail($data['id']);

        if( !$list )
        {
            return false;
        }

        $list->fill($data);
        $list->save();

        if(isset($data['emails']) && !empty($data['emails']))
        {
            $list->emails()->delete();

            foreach($data['emails'] as $email) {
                $list->emails()->save(new \App\Droit\Newsletter\Entities\Newsletter_emails(['list_id' => $list->id, 'email' => $email]));
            }
        }

        if(isset($data['specialisations']) && !empty($data['specialisations'])){
            $list->specialisations()->sync($data['specialisations']);
        }

        return $list;
    }

	public function delete($id){

        $list = $this->list->find($id);

        return $list->delete();
    }
}
