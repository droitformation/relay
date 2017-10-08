<?php

namespace App\Droit\Newsletter\Worker;

use App\Droit\Newsletter\Repo\NewsletterInterface;
use App\Droit\Newsletter\Worker\MailjetServiceInterface;
use App\Droit\Newsletter\Repo\NewsletterUserInterface;
use App\Droit\Newsletter\Repo\NewsletterCampagneInterface;
use App\Droit\Newsletter\Worker\CampagneInterface;
use App\Droit\Service\UploadInterface;
use Maatwebsite\Excel\Excel;

use App\Jobs\SendBulkEmail;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ImportWorker implements ImportWorkerInterface
{
    use DispatchesJobs;

    protected $mailjet;
    protected $subscriber;
    protected $newsletter;
    protected $excel;
    protected $campagne;
    protected $worker;
    protected $upload;

    public function __construct(
        MailjetServiceInterface $mailjet ,
        NewsletterUserInterface $subscriber,
        NewsletterInterface $newsletter,
        Excel $excel,
        NewsletterCampagneInterface $campagne,
        CampagneInterface $worker,
        UploadInterface $upload
    )
    {
        $this->mailjet    = $mailjet;
        $this->newsletter = $newsletter;
        $this->subscriber = $subscriber;
        $this->excel      = $excel;
        $this->campagne   = $campagne;
        $this->worker     = $worker;
        $this->mailjet    = $mailjet;
        $this->upload     = $upload;
    }

    public function import($data,$file)
    {
        $file          = $this->upload->upload( $file , 'files/import' );
        $newsletter_id = isset($data['newsletter_id']) && $data['newsletter_id'] > 0 ? $data['newsletter_id'] : null;

        if(!$file) {
            throw new \App\Exceptions\FileUploadException('Upload failed');
        }

        // path to xls
        $path = public_path('files/import/'.$file['name']);

        // Read uploaded xls
        $results = $this->read($path);

        // we want to import in one of the newsletter subscriber's list
        if($newsletter_id) {
            // Subscribe the new emails
            $this->subscribe($results,$newsletter_id);

            // Store imported file as csv for mailjet sync
            $this->store($path);

            // Mailjet sync
            $this->sync($file['name'], $newsletter_id);
        }

        return $results;
    }

    public function subscribe($results,$list = null)
    {
        foreach($results as $email)
        {
            $subscriber = $this->subscriber->findByEmail($email->email);

            if(!$subscriber)
            {
                $subscriber = $this->subscriber->create([
                    'email'         => $email->email,
                    'activated_at'  => \Carbon\Carbon::now(),
                    'newsletter_id' => $list
                ]);
            }

            $this->subscriber->subscribe($subscriber->id,$list);
        }
    }

    public function read($file)
    {
        $results = $this->excel->load($file, function($reader) {
            $reader->ignoreEmpty();
            $reader->setSeparator('\r\n');
        })->get();


        // If the upload is not formatted correctly redirect back
        if(isset($results) && $results->isEmpty() || !array_has($results->toArray(), '0.email') ) {
            throw new \App\Exceptions\BadFormatException('Le fichier est vide ou mal formaté');
        }
        
        return $results;
    }

    /*
     * Convert to csv
     * */
    public function store($file)
    {
        $this->excel->load($file)->store('csv', public_path('files/import'));
    }

    public function sync($file,$list)
    {
        // Import csv to mailjet
        $newsletter = $this->newsletter->find($list);
        $this->mailjet->setList($newsletter->list_id); // testing list

        $filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);

        $dataID   = $this->mailjet->uploadCSVContactslistData(file_get_contents(public_path('files/import/'.$filename.'.csv')));
        $response = $this->mailjet->importCSVContactslistData($dataID);
    }

    public function send($campagne_id,$list)
    {
        $campagne = $this->campagne->find($campagne_id);
        $html     = $this->worker->html($campagne_id);
        
        if(!$list->emails->isEmpty() && $campagne && $html)
        {
            $recipients = $list->emails->unique()->reject(function ($email, $key) {
                return !filter_var($email->email, FILTER_VALIDATE_EMAIL) || empty($email->email);
            })->map(function ($email) {
                return  ['Email' => $email->email, 'Name'  => ""];
            });

            // Send only 100 at the time to avoid timeout
            // dispatch to jobs
            $chunks = $recipients->chunk(100);

            foreach ($chunks as $chunk){
                $job = (new SendBulkEmail($campagne,$html,$chunk->toArray()));
                $this->dispatch($job);
            }

            return true;
        }

        return true;
    }
}