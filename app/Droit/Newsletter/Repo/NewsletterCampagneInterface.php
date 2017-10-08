<?php namespace App\Droit\Newsletter\Repo;

interface NewsletterCampagneInterface {

	public function getAll();
    public function getAllSent();
	public function getLastCampagne($newsletter_id = null);
	public function getArchives($newsletter_id,$year);
	public function find($data);
	public function archive($id);
	public function create(array $data);
	public function update(array $data);
    public function updateStatus($data);
	public function delete($id);

}
