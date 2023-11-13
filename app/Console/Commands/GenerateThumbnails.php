<?php

namespace App\Console\Commands;

use App\Models\Make;
use App\Models\Photo;
use App\Models\Product;
use App\Models\Replacement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:generate {id?} {--all} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate small,medium large and full-size thumbnail for the given photo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->argument('id') && !$this->option('all')) {
            $this->warn('You need to provide at least one between a specific id or --all option to execute this command');
            return Command::FAILURE;
        }
        Image::configure(['driver' => 'imagick']);
        $force = $this->option('force');
        $all = $this->option('all');

        if ($all) {
            $photo = $force ? Photo::select('*')->where('path', '!=', NULL)->get()
                : Photo::select('*')->where('path', '!=', NULL)->where('has_been_processed', '=', '0')->get();

        } else {
            $photo = Photo::find($this->argument('id'));
            if (!$force && $photo->has_been_processed == 1) {
                $this->warn('This photo has already been processed. If you wish to overwrite , use the command with the --force option');
                return Command::FAILURE;
            }
            $photo = [$photo];
        }

        foreach ($photo as $img) {
            $thmb = Storage::get($img->path);
            $photoParent = $img->photoable;
            if($photoParent instanceof Replacement){

                $photoParent = $photoParent->getKey();

                //Storage::exists('products/skus/'.$photoParent.'/img') ?  null :  Storage::makeDirectory('products/skus/'.$photoParent.'/thumb');

                Storage::put('warehouse/replacements/ids/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '.webp', Image::make($thmb)->encode('webp'));

                Storage::put('warehouse/replacements/ids/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_sm.webp', Image::make($thmb)->resize(150, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 80));

                Storage::put('warehouse/replacements/ids/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_md.webp', Image::make($thmb)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 85));

                Storage::put('warehouse/replacements/ids/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_lg.webp', Image::make($thmb)->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp'));
            }else if($photoParent instanceof Make){

                $photoParent = $photoParent->getKey();

                Storage::put('warehouse/mmv/makes/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '.webp', Image::make($thmb)->encode('webp'));

                Storage::put('warehouse/mmv/makes/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_sm.webp', Image::make($thmb)->resize(150, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 80));

                Storage::put('warehouse/mmv/makes/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_md.webp', Image::make($thmb)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 85));

                Storage::put('warehouse/mmv/makes/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_lg.webp', Image::make($thmb)->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp'));
            }else if($photoParent instanceof Product){

                $photoParent = $photoParent->getKey();

                //Storage::exists('products/skus/'.$photoParent.'/img') ?  null :  Storage::makeDirectory('products/skus/'.$photoParent.'/thumb');

                Storage::put('warehouse/products/skus/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '.webp', Image::make($thmb)->encode('webp'));

                Storage::put('warehouse/products/skus/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_sm.webp', Image::make($thmb)->resize(150, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 80));

                Storage::put('warehouse/products/skus/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_md.webp', Image::make($thmb)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp', 85));

                Storage::put('warehouse/products/skus/' . $photoParent . '/img/' . $photoParent . '_' . $img->id . '_thumbnail_lg.webp', Image::make($thmb)->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('webp'));
            }


            $img->has_been_processed = 1;
            $img->save();
        }
        $this->info('Operation completed successfully');
        return Command::SUCCESS;

    }
}
