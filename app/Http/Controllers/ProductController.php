<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessThumbnails;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Make;
use App\Models\Model;
use App\Models\Photo;
use App\Models\Product;
use App\Models\Replacement;
use App\Models\Side;
use App\Models\Version;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Intervention\Image\ImageManagerStatic as Image;
use mysql_xdevapi\Exception;
use PhpParser\Node\Expr\PreDec;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function index(Request $request){
        /*if($request->get('replacement')){

            if(is_null(Replacement::find($request->get('replacement')))){
                return redirect()->route('warehouse.replacements.index')->with('message', 'Non esiste il ricambio selezionato');
            }

            if($request->get('search')){
                return Inertia::render('Product',[
                    'tab' => 'product',
                    'imgBaseUrl' => url('').'/storage/',
                    'permissions' => $request->user()->can('write_products'),
                    'products' => Product::with(['replacement','replacement.photos','photos'])
                        ->where('replacement_id','=',$request->get('replacement'))
                        ->where(function (Builder $query) use ($request){
                            $query->where('sku','like','%%'.$request->get('search').'%%')->orWhere('notes->'.app()->getLocale(),'like','%%'.$request->get('search').'%%');
                        })
                        ->orWhereHas('replacement',function (Builder $query) use ($request){
                            $query->where('reference','like','%%'.$request->get('search').'%%')
                                ->orWhere('description->'.app()->getLocale(),'like','%%'.$request->get('search').'%%');
                        })->paginate(10),
                    'filters' => [
                        [
                            'key' => __('strings.searchFilter'),
                            'value' => $request->get('search') ?: null,
                        ],
                        [
                            'key' => __('replacement.name'),
                            'value' => Replacement::find($request->get('replacement'))->description,
                        ]
                    ]
                ]);
            }else{

                return Inertia::render('Product',[
                    'tab' => 'product',
                    'imgBaseUrl' => url('').'/storage/',
                    'permissions' => $request->user()->can('write_products'),
                    'products' => Product::with(['replacement','replacement.photos','photos'])
                        ->where('replacement_id','=',$request->get('replacement'))
                        ->paginate(10),
                    'filters' => [
                        [
                            'key' => __('replacement.name'),
                            'value' => Replacement::find($request->get('replacement'))->description,
                        ]
                    ]
                ]);
            }
        }
        if($request->get('search')){
            return Inertia::render('Product',[
                'tab' => 'product',
                'imgBaseUrl' => url('').'/storage/',
                'permissions' => $request->user()->can('write_products'),
                'products' => Product::with(['replacement','replacement.photos','photos'])
                ->where(function (Builder $query) use ($request){
                    $query->where('sku','like','%%'.$request->get('search').'%%')->orWhere('notes->'.app()->getLocale(),'like','%%'.$request->get('search').'%%');
                })
                ->orWhereHas('replacement',function (Builder $query) use ($request){
                    $query->where('reference','like','%%'.$request->get('search').'%%')
                        ->orWhere('description->'.app()->getLocale(),'like','%%'.$request->get('search').'%%');
                })->paginate(10),
                'filters' => [
                    [
                        'key' => __('strings.searchFilter'),
                        'value' => $request->get('search') ?: null,
                    ],
                ]
            ]);
        }
        return Inertia::render('Product',[
            'tab' => 'product',
            'imgBaseUrl' => url('').'/storage/',
            'permissions' => $request->user()->can('write_products'),
            'products' => Product::with(['replacement','replacement.photos','photos'])->paginate(10),
        ]);*/
        return Inertia::render('Product',[
            'tab' => 'product',
            'imgBaseUrl' => url('').'/storage/',
            'permissions' => true,
            'products' => [
                "data" => [
                    [
                        'sku' => 'Prodotto di test',
                        "photos" => [],
                        'replacement' =>
                            [
                                "id" => 1,
                                "make_id" => "M0001",
                                "reference" => "Referenza ricambio di test",
                                "model_id" => "M0001",
                                "version_id" => "V0001",
                                "category_id" => 1,
                                "side_id" => 3,
                                "description" => ["it" => "Descrizione ricambio di test in italiano"],
                                "price" => NULL,
                                "is_active" => 0,
                                "photos" => [["path" => "warehouse/replacements/ids/1/img/originals/1_2.jpg"]]
                        ],
                        'notes' => ['it' => 'Note di test in italiano'],
                        "condition_id" => 0,
                        "price" => 10.00,"deleted_at" => null
                    ]
                ]
            ],
        ]);
    }
    public function newEditIndex(Request $request){
        /*if(!$request->user()->can('write_products')){
            return redirect()->back()->with('error',__('strings.permError'));
        }
        $pagination = Category::with('children')->whereIsRoot()->get();
        $traverse = function ($categories, $depth = 0) use (&$traverse) {
            foreach ($categories as $category) {
                $category->depth = $depth;
                $traverse($category->children, $depth + 1);
            }
        };

        $traverse($pagination);
        if($request->route()->parameter('sku')){
            return Inertia::render('Product',[
                'tab' => 'product/edit',
                'makes' => Make::with(['models','models.versions'])->get(),
                'models' => Model::with('versions')->get(),
                'versions' => Version::all(),
                'imgBaseUrl' => url('').'',
                'categories' => $pagination,
                'sides' => Side::all(),
                'conditions' => Condition::all(),
                'replacements' => Replacement::with(['version','version.make','version.model','photos','category','side','attributeValues'])->get(),
                'productEdit' => Product::with(['replacement','replacement.photos','photos','attributeValues'])->find($request->route()->parameter('sku')),

            ]);
        }else{
            return Inertia::render('Product',[
                'tab' => 'product/new',
                'makes' => Make::with(['models','models.versions'])->get(),
                'models' => Model::with('versions')->get(),
                'versions' => Version::all(),
                'imgBaseUrl' => url('').'',
                'categories' => $pagination,
                'sides' => Side::all(),
                'conditions' => Condition::all(),
                'replacements' => Replacement::with(['version','version.make','version.model','photos','category','side','attributeValues'])->get(),

            ]);
        }*/
        if($request->route()->parameter('sku')){

            return Inertia::render('Product',[
                'tab' => 'product/edit',
                'makes' => [
                    [
                        "id" => "M0001",
                        "name" => "Marca di test",
                        "slug" => "marca_di_test",
                        "is_active" => 0,
                        "models" => []
                    ]
                ],
                'models' => [
                    [
                        "id" => "M0001",
                        "make_id" => "M0001",
                        "name" => "Modello di test",
                        "slug" => "modello_di_test",
                        "is_active" => 0,
                    ]
                ],
                'versions' => [
                    [
                        "id" => "V0001",
                        "make_id" => "M0001",
                        "model_id" => "M0001",
                        "name" => "Versione di test",
                        "slug" => "versione_di_test",
                        "is_active" => 0,
                    ]
                ],
                'imgBaseUrl' => url('').'',
                'categories' => [],
                'sides' => [
                        [
                            "id" => 1,
                            "value" => ["it" => "Sinistra"]
                        ],
                        [
                            "id" => 2,
                            "value" => ["it" => "Destra"]
                        ],
                        [
                            "id" => 3,
                            "value" => ["it" => "Neutro"]
                        ],
                ],
                'conditions' => [
                    [
                        "id" => 1,
                        "value" => ["it" => "Come Nuovo"]
                    ],
                    [
                        "id" => 2,
                        "value" => ["it" => "Buone condizioni"]
                    ],
                    [
                        "id" => 3,
                        "value" => ["it" => "Danneggiato"]
                    ],
                ],
                'replacements' =>  [[
                    "id" => 1,
                    "make_id" => "M0001",
                    "attribute_values" => [],
                    "reference" => "Referenza ricambio di test",
                    "model_id" => "M0001",
                    "version_id" => "V0001",
                    'version' => [
                        "id" => "V0001",
                        "make_id" => "M0001",
                        "model_id" => "M0001",
                        "make" => [
                            "id" => "M0001",
                            "name" => "Marca di test",
                            "slug" => "marca_di_test",
                            "is_active" => 0,
                        ],
                        "model" => [
                            "id" => "M0001",
                            "make_id" => "M0001",
                            "name" => "Modello di test",
                            "slug" => "modello_di_test",
                            "is_active" => 0,
                        ],
                        "name" => "Versione di test",
                        "slug" => "versione_di_test",
                        "is_active" => 0,
                    ],
                    "category_id" => 1,
                    "side_id" => 3,
                    "description" => ["it" => "Descrizione ricambio di test in italiano"],
                    "price" => NULL,
                    "is_active" => 0,
                    "photos" => [["path" => "warehouse/replacements/ids/1/img/originals/1_2.jpg"]]
                ]],
                'productEdit' => [
                    'sku' => 'Prodotto di test',
                    "photos" => [],
                    'replacement' =>
                        [
                            "id" => 1,
                            "make_id" => "M0001",
                            "reference" => "Referenza ricambio di test",
                            "model_id" => "M0001",
                            "version_id" => "V0001",
                            "category_id" => 1,
                            "side_id" => 3,
                            "description" => ["it" => "Descrizione ricambio di test in italiano"],
                            "price" => NULL,
                            "is_active" => 0,
                            "photos" => [["path" => "warehouse/replacements/ids/1/img/originals/1_2.jpg"]]
                        ],
                    'notes' => ['it' => 'Note di test in italiano'],
                    "condition_id" => 0,
                    "price" => 10.00,"deleted_at" => null
                ],

            ]);
        }else{

            return Inertia::render('Product',[
                'tab' => 'product/new',
                "categories" => [],
                'makes' => [
                    [
                        "id" => "M0001",
                        "name" => "Marca di test",
                        "slug" => "marca_di_test",
                        "is_active" => 0,
                        "models" => []
                    ]
                ],
                'models' => [
                    [
                        "id" => "M0001",
                        "make_id" => "M0001",
                        "name" => "Modello di test",
                        "slug" => "modello_di_test",
                        "is_active" => 0,
                    ]
                ],
                'versions' => [
                    [
                        "id" => "V0001",
                        "make_id" => "M0001",
                        "model_id" => "M0001",
                        "make" => [
                            "id" => "M0001",
                            "name" => "Marca di test",
                            "slug" => "marca_di_test",
                            "is_active" => 0,
                        ],
                        "model" => [
                            "id" => "M0001",
                            "make_id" => "M0001",
                            "name" => "Modello di test",
                            "slug" => "modello_di_test",
                            "is_active" => 0,
                        ],
                        "name" => "Versione di test",
                        "slug" => "versione_di_test",
                        "is_active" => 0,
                    ]
                ],
                'imgBaseUrl' => url('').'',
                //'categories' => $pagination,
                'sides' => [
                    [
                        "id" => 1,
                        "value" => ["it" => "Sinistra"]
                    ],
                    [
                        "id" => 2,
                        "value" => ["it" => "Destra"]
                    ],
                    [
                        "id" => 3,
                        "value" => ["it" => "Neutro"]
                    ],
                ],
                'conditions' => [
                    [
                        "id" => 1,
                        "value" => ["it" => "Come Nuovo"]
                    ],
                    [
                        "id" => 2,
                        "value" => ["it" => "Buone condizioni"]
                    ],
                    [
                        "id" => 3,
                        "value" => ["it" => "Danneggiato"]
                    ],
                ],
                'replacements' =>  [[
                    "id" => 1,
                    "make_id" => "M0001",
                    "attribute_values" => [],
                    "reference" => "Referenza ricambio di test",
                    "model_id" => "M0001",
                    "version_id" => "V0001",
                    'version' => [
                        "id" => "V0001",
                        "make_id" => "M0001",
                        "model_id" => "M0001",
                        "make" => [
                            "id" => "M0001",
                            "name" => "Marca di test",
                            "slug" => "marca_di_test",
                            "is_active" => 0,
                        ],
                        "model" => [
                            "id" => "M0001",
                            "make_id" => "M0001",
                            "name" => "Modello di test",
                            "slug" => "modello_di_test",
                            "is_active" => 0,
                        ],
                        "name" => "Versione di test",
                        "slug" => "versione_di_test",
                        "is_active" => 0,
                    ],
                    "category_id" => 1,
                    "side_id" => 3,
                    "description" => ["it" => "Descrizione ricambio di test in italiano"],
                    "price" => NULL,
                    "is_active" => 0,
                    "photos" => [["path" => "warehouse/replacements/ids/1/img/originals/1_2.jpg"]]
                ]],

            ]);
        }
    }
    public function store(Request $request){
        if(!$request->user()->can('write_products')){
            return redirect()->back()->with('error',__('strings.permError'));
        }

        $attributeValues = $request->get('attributeInputs',[]);
        $rules = [];
        $rules['sku'] = 'required';
        $rules['replacement'] = 'required';
        $rules['condition'] = 'required';
        foreach ($attributeValues as $key=>$value){
            if(Attribute::find($key)->is_required){
                $rules['attributeInputs.'.$key] = 'required';
            }
        }
        $request->validate($rules);

        DB::beginTransaction();
        try{
            $sku = $request->get('sku');

            $product = Product::create([
                'sku' => $sku,
                'replacement_id' => $request->get('replacement'),
                'notes' => $request->get('notes'),
                'condition_id' => $request->get('condition'),
                'price' => $request->get('price'),
            ]);

            if($request->files->get('images')){
                Image::configure(['driver' => 'imagick']);
                $images = $request->files->get('images');
                $images = array_filter($images, function ($img) {
                    return (substr(Image::make($img)->mime(),0,5) == 'image');
                });
                foreach($images as $img){


                    if(is_null($product)) {
                        throw new NotFoundHttpException('Product non trovato');
                    }
                    if(!Storage::exists('warehouse')){
                        Storage::makeDirectory('warehouse');
                    }
                    if(!Storage::exists('warehouse/products')){
                        Storage::makeDirectory('warehouse/products');
                    }
                    if(!Storage::exists('warehouse/products/skus')){
                        Storage::makeDirectory('warehouse/products/skus');
                    }
                    if(!Storage::exists('warehouse/products/skus/'.$sku)){
                        Storage::makeDirectory('warehouse/products/skus/'.$sku);
                    }
                    if(!Storage::exists('warehouse/products/skus/'.$sku.'/img')){
                        Storage::makeDirectory('warehouse/products/skus/'.$sku.'/img');
                    }
                    if(!Storage::exists('warehouse/products/skus/'.$sku.'/img/originals')){
                        Storage::makeDirectory('warehouse/products/skus/'.$sku.'/img/originals');
                    }

                    $photo = $product->photos()->create([]);

                    $photo->save();

                    $path = 'warehouse/products/skus/'.$sku.'/img/originals/'.$sku.'_'.$photo->id.'.jpg';
                    Storage::put($path,Image::make($img)->encode('jpg'));

                    $photo->path = $path;
                    $photo->save();
                }
            }


            foreach($attributeValues as $key=>$value){
                if($value){
                    $attr = Attribute::find($key);
                    if($attr->type_id == 1){
                        $opt = AttributeOption::create([
                            'attribute_id' => $key,
                            'value' => $value
                        ]);
                        $product->attributeValues()->create([
                            'attribute_id' => $key,
                            'attribute_option_id' => $opt->id
                        ]);
                    }else if ($attr->type_id == 2){
                        $product->attributeValues()->create([
                            'attribute_id' => $key,
                            'attribute_option_id' => $value
                        ]);
                    }
                }
            }

            DB::commit();


        }catch(\Exception $e){
            DB::rollBack();
            return response()->json( $e->getMessage(), 400);
        }

        return redirect()->route('warehouse.products.index')->with('message', __('product.addedProduct'));
    }

    public function update(Request $request){
        if(!$request->user()->can('write_products')){
            return redirect()->back()->with('error',__('strings.permError'));
        }
        $product = Product::find($request->route()->parameter('product'));

        foreach($product->photos as $photo){
            $found = false;
            if($request->get('images') &&  count($request->get('images')) >= 1){
                foreach ($request->get('images') as $image){
                    if($photo->id == $image['id']){
                        $found = true;
                    }
                }
            }
            if(!$found){
                $photo->has_been_processed = 0;
                $photo->save();
                $photo->delete();
            }
        }



        $attributeValues = $request->get('attributeInputs',[]);
        $rules = [];
        $rules['sku'] = 'required';
        $rules['replacement'] = 'required';
        $rules['condition'] = 'required';
        foreach ($attributeValues as $key=>$value){
            if(Attribute::find($key)->is_required){
                $rules['attributeInputs.'.$key] = 'required';
            }
        }
        $request->validate($rules);
        $images = $request->files->get('images');
        if(!is_null($images)){
            Image::configure(['driver' => 'imagick']);

            $images = array_filter($images, function ($img) {
                return (substr(Image::make($img)->mime(),0,5) == 'image');
            });
        }


        DB::beginTransaction();
        try {

            $sku = $request->get('sku');
            $product->sku = $sku;
            $product->condition_id = $request->get('condition');
            $product->replacement_id = $request->get('replacement');
            $product->notes = $request->get('notes');
            $product->price = $request->get('price');

            $product->save();

            $attributes = Product::find($request->route()->parameter('product'))->replacement->category->attributes;
            $attributes = $attributes->merge(Attribute::whereDoesntHave('categories')->get());
            foreach($attributes as $attribute){
                if(count($attribute->attributeValues) == 0){
                    foreach($request->get('attributeInputs') as $key=>$value){
                        if($attribute->id == $key){

                            if($value){
                                if($attribute->type_id == 1){
                                    $opt = AttributeOption::create([
                                        'attribute_id' => $key,
                                        'value' => $value
                                    ]);
                                    $product->attributeValues()->create([
                                        'attribute_id' => $key,
                                        'attribute_option_id' => $opt->id
                                    ]);
                                }else if ($attribute->type_id == 2){
                                    $product->attributeValues()->create([
                                        'attribute_id' => $key,
                                        'attribute_option_id' => $value
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    foreach($attribute->attributeValues as $value){
                        if($value->attributable_id == $request->route()->parameter('product')){
                            foreach($request->get('attributeInputs') as $key=>$attributeInput){
                                if($key == $attribute->id){
                                    if($attributeInput){
                                        if($attribute->type_id == 1){
                                            $opt = AttributeOption::find($value->attribute_option_id);
                                            $opt->value = $attributeInput;
                                            $opt->save();
                                        }else if($attribute->type_id == 2){
                                            $value->attribute_option_id = $attributeInput;
                                            $value->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }

            if(!is_null($images)){
                foreach($images as $img){


                    if(is_null($product)) {
                        throw new NotFoundHttpException('Product non trovato');
                    }
                    if(!Storage::exists('warehouse')){
                        Storage::makeDirectory('warehouse');
                    }
                    if(!Storage::exists('warehouse/products')){
                        Storage::makeDirectory('warehouse/products');
                    }
                    if(!Storage::exists('warehouse/products/skus')){
                        Storage::makeDirectory('warehouse/products/skus');
                    }
                    if(!Storage::exists('warehouse/products/skus/'.$sku)){
                        Storage::makeDirectory('warehouse/products/skus/'.$sku);
                    }
                    if(!Storage::exists('warehouse/products/skus/'.$sku.'/img')){
                        Storage::makeDirectory('warehouse/products/skus/'.$sku.'/img');
                    }
                    if(!Storage::exists('warehouse/products/skus/'.$sku.'/img/originals')){
                        Storage::makeDirectory('warehouse/products/skus/'.$sku.'/img/originals');
                    }

                    $photo = $product->photos()->create([]);

                    $photo->save();

                    $path = 'warehouse/products/skus/'.$sku.'/img/originals/'.$sku.'_'.$photo->id.'.jpg';
                    Storage::put($path,Image::make($img)->encode('jpg'));

                    $photo->path = $path;
                    $photo->save();
                }
                DB::commit();
            }else{
                DB::commit();
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json( $e->getMessage(), 400);
        }


        return redirect()->route('warehouse.products.index')->with('message', __('product.editedProduct'));

    }

    public function destroy(Request $request){
        if(!$request->user()->can('write_products')){
            return redirect()->back()->with('error',__('strings.permError'));
        }
        $product = Product::find($request->route()->parameter('product'));

        $product->delete();
        return redirect()->back()->with('message', __('product.deletedProduct'));

    }
    public function destroyBulk(Request $request){

        if(!$request->user()->can('write_products')){
            return redirect()->back()->with('error',__('strings.permError'));
        }
        if(!$request->user()->can('write_replacements')){
            return redirect()->back()->with('error',__('strings.permError'));
        }
        $skus = $request->route()->parameter('skus');
        $skus = explode(',',$skus);
        Product::destroy($skus);
        return redirect()->back()->with('message', __('product.deletedProduct'));
    }

}
