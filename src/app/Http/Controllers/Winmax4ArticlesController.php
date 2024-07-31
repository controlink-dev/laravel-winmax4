<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Http\Request;

class Winmax4ArticlesController extends Controller
{
    protected $winmax4Service;

    /**
     * Winmax4Controller constructor.
     *
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Setting::where(config('winmax4.license_column'), session(config('winmax4.license_session_key')))->first();

        if(!$winmaxSettings) {
            $this->winmax4Service = new Winmax4Service(true);
        }else{
            $this->winmax4Service = new Winmax4Service(
                false,
                $winmaxSettings->url,
                $winmaxSettings->company_code,
                $winmaxSettings->username,
                $winmaxSettings->password,
                $winmaxSettings->n_terminal
            );
        }
    }

    /**
     * Get articles from Winmax4 API
     */
    public function getArticles(){
        return response()->json(Winmax4Article::get(), 200);
    }

    /**
     * Post articles to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postArticles(Request $request)
    {
        return response()->json($this->winmax4Service->postArticles($request->all()), 200);
    }

    /**
     * Put articles to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function putArticles(Request $request){
        return response()->json($this->winmax4Service->putArticles($request->all()), 200);
    }

    /**
     * Delete articles from Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteArticles(Request $request)
    {
        return response()->json($this->winmax4Service->deleteArticles($request->all()), 200);
    }
}
