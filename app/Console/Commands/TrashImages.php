<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\Product;
use App\Models\Replacement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class TrashImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:trash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move to trash all soft-deleted images (it is possible to restore them up to 30 days after their initial deletion)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Image::configure(['driver' => 'imagick']);
        $images = Photo::onlyTrashed()->where('has_been_processed','=',0)->get();
        if(is_null($images)){
            $this->info('Command ended because there are currently no images to be processed');
            return Command::SUCCESS;
        }

        DB::beginTransaction();
        try{
            foreach($images as $image){
                Storage::move($image->path,'trashed/'.$image->path);
                $image->has_been_processed = 1;
                $image->path = 'trashed/'.$image->path;
                if($image->photoable instanceof Product){
                    $path = 'warehouse/products/skus/'.$image->photoable_id.'/img';
                    $files = Storage::files($path);
                    foreach($files as $file){

                        $fileName = explode($path.'/',$file)[1];
                        if (substr($fileName, 0, strlen($image->photoable_id.'_'.$image->id )) === $image->photoable_id.'_'.$image->id) {
                            Storage::delete($file);
                        }
                    }
                }else if($image->photoable instanceof Replacement){
                    $path = 'warehouse/replacements/ids/'.$image->photoable_id.'/img';
                    $files = Storage::files($path);
                    foreach($files as $file){

                        $fileName = explode($path.'/',$file)[1];
                        if (substr($fileName, 0, strlen($image->photoable_id.'_'.$image->id )) === $image->photoable_id.'_'.$image->id) {
                            Storage::delete($file);
                        }
                    }

                }
                $image->save();
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
        }

        return Command::SUCCESS;
    }
}
