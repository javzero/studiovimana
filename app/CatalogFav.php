<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CatalogFav extends Model
{
    protected $table = "catalog_favs";

    protected $fillable = ['client_id', 'article_id'];

    public function client()
	{
	   	return $this->belongsTo('App\Client', 'id');
    }
    
    public function article()
    {
        return $this->belongsTo('App\CatalogArticle');
    }
}