<?php

namespace App\Http\Controllers\Catalog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CatalogCategory;
use App\CatalogTag;
use App\CatalogArticle;
use App\CatalogFav;
use App\CatalogImage;
use App\CatalogAtribute1;
use File;
use PDF;
use Excel;

class ArticlesController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $code     = $request->get('code');
        $name     = $request->get('name');
        $category = $request->get('category');
        
        if(isset($code) || isset($name) || isset($category))
        {
            $articles = CatalogArticle::search($code, $name, $category)->orderBy('id', 'ASC')->paginate(10); 
        } else {
            $articles = CatalogArticle::orderBy('id', 'DESCC')->paginate(10);    
        }

        //$cats = CatalogCategory::orderBy('id','ASC')->get();
        $categories = CatalogCategory::orderBy('id','ASC')->pluck('name','id');

        return view('vadmin.catalog.index')
            ->with('articles', $articles)
            ->with('categories', $categories);
    }

    public function show($id)
    {
        $article = CatalogArticle::find($id);
        if($article == null){
            return back();
        } else {
            return view('vadmin.catalog.show')->with('article', $article);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    public function exportPdf($params)
    {   
        $items = $this->getData($params);

        $pdf = PDF::loadView('vadmin.catalog.invoice', array('items' => $items));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('listado-catalogo.pdf');
    }

    public function exportExcel($params)
    {
        $items = $this->getData($params);
        
		Excel::create('Excel', function($excel) use($items){
            $excel->sheet('Listado', function($sheet) use($items) {   
                $sheet->loadView('vadmin.catalog.invoice-excel', 
                compact('items'));
            });
        })->export('xls');       
    }

    public function getData($params)
    {
        if($params == 'all'){
            $items = CatalogArticle::orderBy('id', 'DESCC')->paginate(15);    
            return $items;
        }

        parse_str($params , $query);

        $code     = $query['code'];
        $name     = $query['name'];
        $category = $query['category'];

        if(isset($code) || isset($name) || isset($category))
        {
            $items = CatalogArticle::search($code, $name, $category)->orderBy('id', 'ASC')->paginate(15); 
        } else {
            $items = CatalogArticle::orderBy('id', 'DESCC')->paginate(15);    
        }

        return $items;
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $categories = CatalogCategory::orderBy('name', 'ASC')->pluck('name', 'id');
        $atribute1  = CatalogAtribute1::orderBy('name', 'ASC')->pluck('name', 'id');
        $tags       = CatalogTag::orderBy('name', 'ASC')->pluck('name', 'id');
        return view('vadmin.catalog.create')
            ->with('categories', $categories)
            ->with('atribute1', $atribute1)
            ->with('tags', $tags);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name'                 => 'required|min:4|max:250|unique:catalog_articles',
            'code'                 => 'unique:catalog_articles,code',
            'category_id'          => 'required',
            'slug'                 => 'required|alpha_dash|min:4|max:255|unique:catalog_articles,slug',
            'image'                => 'image',

        ],[
            'name.required'        => 'Debe ingresar un nombre',
            'name.min'             => 'El título debe tener al menos 4 caracteress',
            'name.max'             => 'El título debe tener como máximo 250 caracteress',
            'name.unique'          => 'El título ya existe en otro artículo',
            'code.unique'          => 'El código está utilizado por otro producto',
            'category_id.required' => 'Debe ingresar una categoría',
            'slug.required'        => 'Se requiere un slug',
            'slug.min'             => 'El slug debe tener 4 caracteres como mínimo',
            'slug.max'             => 'El slug debe tener 255 caracteres como máximo',
            'slug.max'             => 'El slug debe tener guiones bajos en vez de espacios',
            'slug.unique'          => 'El slug debe ser único, algún otro artículo lo está usando',
            'image'                => 'El archivo adjuntado no es soportado',
        ]);
        
        if($request->discount == null){
            $request->discount = '0';
        }
        
        $article           = new CatalogArticle($request->all());
        $article->user_id  = \Auth::guard('user')->user()->id;
        
        $images            = $request->file('images');
        $thumbnail         = $request->file('thumbnail');
        $imgPath           = public_path("webimages/catalogo/"); 
        $extension         = '.jpg';
        
        $number = '2';

        if($article->save()){
            $article->atribute1()->sync($request->atribute1);
            $article->tags()->sync($request->tags);
            
            $thumbHeight = 360;
            $thumbWidth = 240;
            $imgHeight = 750;
            $imgWidth = 500;

            try {
                if($thumbnail){
                    $thumb = \Image::make($thumbnail);
                    $thumb->encode('jpg', 80)->fit($thumbWidth, $thumbHeight)->save($imgPath.$article->id.'-0'.$extension);
                    $article->thumb    = $article->id.'-0'.$extension;
                    $article->save();

                    $thumbToImg = \Image::make($thumbnail);
                    $image = new CatalogImage();
                    $image->name = $article->id.'-1'.$extension;
                    $thumbToImg->encode('jpg', 80)->fit($imgWidth, $imgHeight)->save($imgPath.$image->name);
                    $image->article()->associate($article);
                    $image->save();
                }

                if($images){
                    foreach($images as $phisic_image)
                    {
                        $filename = $article->id.'-'.$number++;
                        $img = \Image::make($phisic_image);
                        $img->encode('jpg', 80)->fit($imgWidth, $imgHeight)->save($imgPath.$filename.$extension);
                        
                        $image = new CatalogImage();
                        $image->name = $filename.$extension;
                        $image->article()->associate($article);
                        $image->save();
                    }
                }

            } catch(\Exception $e) {
                $article->delete();
                return redirect()->route('catalogo.index')->with('message','Error al crear el artículo: '. $e);
            }
        }
    
        return redirect()->route('catalogo.index')->with('message','Item creado');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {   
        $tags       = CatalogTag::orderBy('name', 'DESC')->pluck('name', 'id');
        $atribute1  = CatalogAtribute1::orderBy('name', 'DESC')->pluck('name', 'id');
        $article    = CatalogArticle::find($id);
        $categories = CatalogCategory::orderBy('name', 'DESC')->pluck('name', 'id');
        $article->each(function($article){
                $article->images;
        });

        return view('vadmin.catalog.edit')
            ->with('categories', $categories)
            ->with('article', $article)
            ->with('tags', $tags)
            ->with('atribute1', $atribute1);
    }

    public function update(Request $request, $id)
    {
        $article   = CatalogArticle::find($id);

        $this->validate($request,[
            'name'                 => 'required|min:4|max:250|unique:catalog_articles,name,'.$article->id,
            'code'                 => 'unique:catalog_articles,code,'.$article->id,
            'category_id'          => 'required',
            'slug'                 => 'required|alpha_dash|min:4|max:255|unique:catalog_articles,slug,'.$article->id,
            'image'                => 'image',

        ],[
            'name.required'        => 'Debe ingresar un nombre',
            'name.min'             => 'El título debe tener al menos 4 caracteress',
            'name.max'             => 'El título debe tener como máximo 250 caracteress',
            'name.unique'          => 'El título ya existe en otro artículo',
            'code.unique'          => 'El código está utilizado por otro producto',
            'category_id.required' => 'Debe ingresar una categoría',
            'slug.required'        => 'Se requiere un slug',
            'slug.min'             => 'El slug debe tener 4 caracteres como mínimo',
            'slug.max'             => 'El slug debe tener 255 caracteres como máximo',
            'slug.max'             => 'El slug debe tener guiones bajos en vez de espacios',
            'slug.unique'          => 'El slug debe ser único, algún otro artículo lo está usando',
            'image'                => 'El archivo adjunto no es soportado',
        ]);

        $article->fill($request->all());
        
        $images    = $request->file('images');
        $thumbnail = $request->file('thumbnail');
        $imgPath   = public_path("webimages/catalogo/"); 
        $extension = '.jpg';

        $thumbHeight = 360;
        $thumbWidth = 240;
        $imgHeight = 750;
        $imgWidth = 500;
               
        $article->thumb = $article->id.'-0'.$extension;
        if($article->save()){
            $article->atribute1()->sync($request->atribute1);
            $article->tags()->sync($request->tags);
            
            if(!$article->images->isEmpty()){
                $number = $article->images->last()->name;
                $number = explode('-',$number);
                $number = explode('.',$number[1]);
                $number = ($number[0]+'1');
            } else {
                $number = '1';
            }

                    
            try {
                if($thumbnail){
                    $thumb = \Image::make($thumbnail);
                    $thumb->encode('jpg', 80)->fit(250, 250)->save($imgPath.$article->id.'-0'.$extension);
                    $article->thumb    = $article->id.'-0'.$extension;
                    $article->save();

                    $thumbToImg = \Image::make($thumbnail);
                    $image       = new CatalogImage();
                    $image->name = $article->id.'-1'.$extension;
                    $thumbToImg->encode('jpg', 80)->fit(800, 800)->save($imgPath.$image->name);
                    $image->article()->associate($article);
                    $image->save();
                }

                if($images){
                
                    foreach($images as $phisic_image)
                    {
                        $filename = $article->id.'-'.$number++;
                        $img      = \Image::make($phisic_image);
                        $img->encode('jpg', 80)->fit(800, 800)->save($imgPath.$filename.$extension);
                        
                        $image            = new CatalogImage();
                        $image->name      = $filename.$extension;
                        $image->article()->associate($article);
                        $image->save();
                    }
                }

            } catch(\Exception $e) {
                $article->delete();
                return redirect()->route('catalogo.index')->with('message','Error al crear el item: '. $e);
            }
        }

        return redirect()->route('catalogo.index')->with('message', 'Se ha editado el item con éxito');
    }

    public function updateStatus(Request $request, $id)
    {
            $article = CatalogArticle::find($id);
            $article->status = $request->status;
            $article->save();

            return response()->json([
                "lastStatus" => $article->status,
            ]);
    }

    public function updateStock(Request $request, $id)
    {   
        $item          = CatalogArticle::find($id);
        $item->stock   = $request->value;
        $item->save();

        return response()->json([
            "response" => '1',
            "newstock" => $item->stock,
            "action"   => $request->action
        ]);
    }

    public function updatePrice(Request $request, $id)
    {   
        $item          = CatalogArticle::find($id);
        $item->price   = $request->value;
        $item->save();

        return response()->json([
            "response" => '1',
            "newprice" => $item->price,
            "action"   => $request->action
        ]);
    }

    public function updateDiscount(Request $request, $id)
    {   
        $item          = CatalogArticle::find($id);
        $item->discount   = $request->value;
        $item->save();

        return response()->json([
            "response" => '1',
            "newdiscount" => $item->discount,
            "action"   => $request->action
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request)
    {   
        $ids      = json_decode('['.str_replace("'",'"',$request->id).']', true);
        $path     = 'webimages/catalogo/';

        try {

            foreach ($ids as $id) {
                // Check existence of related favs and delete
                $relatedFavs = CatalogFav::where('article_id', $id)->get();
                if(!$relatedFavs->isEmpty()){
                    foreach($relatedFavs as $relatedFav){
                        $relatedFav->delete();
                    }
                }
                $record = CatalogArticle::find($id);
                $record->tags()->detach();
                $record->atribute1()->detach();
                
                $images = $record->images;
                File::Delete(public_path( $path . $record->thumb));
                foreach ($images as $image) {
                    File::Delete(public_path( $path . $image->name));
                }

                $delete = $record->delete();
            }
            return response()->json([
                'success'   => true,
            ]); 
        }  catch (\Exception $e) {
            return response()->json([
                'success'   => false,
                'error'    => 'Error: '.$e
            ]);    
        }
    }
}
