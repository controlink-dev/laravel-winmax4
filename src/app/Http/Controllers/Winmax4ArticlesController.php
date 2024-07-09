<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

abstract class Winmax4ArticlesController
{
    /**
     * Get articles from Winmax4 API
     */
    public function getArticles(){
        //return response()->json(Winmax4Article::get(), 200);
    }
}
