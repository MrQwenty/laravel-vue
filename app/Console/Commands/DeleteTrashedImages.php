<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\Product;
use App\Models\Replacement;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeleteTrashedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:deleteTrashed {days?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Completely delete all trashed images that have been trashed for more than 30 days (days are otherwise to be specified in command)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = 30;
        $all = $this->option('all');
        if($all) $days = 0;
        else if ($this->argument('days')) $days = $this->argument('days');

        $photos = Photo::onlyTrashed()->where('has_been_processed','=',1)->get();
        $limit = Carbon::now()->subDays($days)->timestamp;
        foreach($photos as $photo){
            if( $limit > $photo->deleted_at->timestamp ){
                if($photo->photoable()->withTrashed()->first() instanceof Replacement){
                    Storage::delete($photo->path);
                    if(count(Storage::files('warehouse/replacements/ids/'.$photo->photoable_id.'/img/originals')) == 0){
                        Storage::deleteDirectory('warehouse/replacements/ids/'.$photo->photoable_id);
                    }
                    if(count(Storage::files('trashed/warehouse/replacements/ids/'.$photo->photoable_id.'/img/originals')) == 0){
                        Storage::deleteDirectory('trashed/warehouse/replacements/ids/'.$photo->photoable_id);
                    }
                    $photo->forceDelete();

                }else if ($photo->photoable()->withTrashed()->first() instanceof Product){
                    Storage::delete($photo->path);
                    if(count(Storage::files('warehouse/products/skus/'.$photo->photoable_id.'/img/originals')) == 0){
                        Storage::deleteDirectory('warehouse/products/skus/'.$photo->photoable_id);
                    }
                    if(count(Storage::files('trashed/warehouse/products/skus/'.$photo->photoable_id.'/img/originals')) == 0){
                        Storage::deleteDirectory('trashed/warehouse/products/skus/'.$photo->photoable_id);
                    }
                    $photo->forceDelete();
                }
            }
        }

        return Command::SUCCESS;
    }
}
