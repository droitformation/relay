<?php namespace App\Droit\Newsletter\Repo;

interface NewsletterSubscriptionInterface {

	public function getAll($newsletter_id);
	public function find($id);
	public function delete($id);

}
