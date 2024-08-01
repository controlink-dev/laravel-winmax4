<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4ArticleService;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Http\JsonResponse;
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
            $this->winmax4Service = new Winmax4ArticleService(true);
        }else{
            $this->winmax4Service = new Winmax4ArticleService(
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
     * Get articles from the Winmax4 API.
     *
     * This method queries the Winmax4Article model using Eloquent ORM to fetch
     * all records from the corresponding database table. It then returns these records
     * as a JSON response with an HTTP status code of 200 (OK).
     *
     * ### Usage Example
     *
     * ```php
     * $response = $this->getArticles();
     * ```
     *
     * ### Response Format
     *
     * The response is a JSON-encoded array of objects, each representing a record from the
     * Winmax4Article model. The format of each object corresponds to the columns of the
     * underlying database table.
     *
     * ### Return Type
     *
     * | Type          | Description                                                     |
     * |---------------|-----------------------------------------------------------------|
     * | `JsonResponse`| A JSON response containing the fetched taxes with status 200.|
     *
     * ### Possible Exceptions
     *
     * This method generally doesn't throw exceptions directly, but underlying database
     * connectivity issues or application errors might trigger exceptions at a higher level.
     *
     * @return JsonResponse Returns a JSON response with all articles.
     */
    public function getArticles(): JsonResponse
    {
        return response()->json(Winmax4Article::get(), 200);
    }


    public function postArticles(Request $request)
    {
        return response()->json($this->winmax4Service->postArticles($request->all()), 200);
    }


    public function putArticles(Request $request){
        return response()->json($this->winmax4Service->putArticles($request->all()), 200);
    }


    public function deleteArticles(Request $request)
    {
        return response()->json($this->winmax4Service->deleteArticles($request->all()), 200);
    }
}
