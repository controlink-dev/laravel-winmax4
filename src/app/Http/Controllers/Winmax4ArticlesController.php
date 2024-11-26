<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Services\Winmax4ArticleService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    //TODO: Implement the postArticles method, putArticles method, and deleteArticles method when the Winmax4 API is available.
    /**
     * Post articles to the Winmax4 API.
     *
     * This method validates the request data and sends the article information to the Winmax4 API.
     * It expects specific input fields and ensures they conform to the validation rules defined within.
     *
     * ### Request Validation
     *
     * | Parameter       | Type     | Rules                                   | Description                                    |
     * |-----------------|----------|-----------------------------------------|------------------------------------------------|
     * | code            | string   | required                                | The article code.                              |
     * | designation     | string   | required                                | The article designation.                       |
     * | familyCode      | string   | required                                | The article family code.                       |
     * | subFamilyCode   | string   | nullable                                | The article sub-family code.                   |
     * | subSubFamilyCode| string   | nullable                                | The article sub-sub-family code.               |
     * | vatCode         | string   | required                                | The article VAT code.                          |
     * | vatRate         | string   | required                                | The article VAT rate.                          |
     * | first_price     | string   | required                                | The article first price.                       |
     * | second_price    | string   | required                                | The article second price.                      |
     * | has_stock       | boolean  | required_if:has_stock,1                 | The article stock status.                      |
     * | stock           | integer  | required_if:has_stock,1                 | The article stock quantity.                    |
     *
     * ### Entity Type Values
     *
     * | Type                  | Value |
     * |-----------------------|-------|
     * | Product               | 1     |
     * | Service               | 2     |
     *
     * @param Request $request The HTTP request instance containing all required data.
     * @return JsonResponse Returns a JSON response with the API result.
     * @throws GuzzleException If an error occurs during the API request.
     */
    public function postArticles(Request $request): JsonResponse{
        $request->validate([
            'code' => 'required|string',
            'designation' => 'required|string',
            'familyCode' => 'required|string',
            'subFamilyCode' => 'nullable|string',
            'subSubFamilyCode' => 'nullable|string',
            'vatCode' => 'required|string',
            'vatRate' => 'required|string',
            'first_price' => 'required|string',
            'second_price' => 'required|string',
            'stock' => 'nullable|string',
        ]);

        return response()->json($this->winmax4Service->postArticles(
            $request->code,
            $request->designation,
            $request->familyCode,
            $request->subFamilyCode,
            $request->subSubFamilyCode,
            $request->vatCode,
            $request->vatRate,
            $request->first_price,
            $request->second_price,
            $request->stock,
            $request->is_active
        ), 200);
    }

    /**
     * Update articles in the Winmax4 API.
     *
     * This method validates the request data and sends the article information to the Winmax4 API
     * for updating an existing article. It expects specific input fields and ensures they conform
     * to the validation rules defined within.
     *
     * ### Request Validation
     *
     * | Parameter       | Type     | Rules                                   | Description                                    |
     * |-----------------|----------|-----------------------------------------|------------------------------------------------|
     * | code            | string   | required                                | The article code.                              |
     * | designation     | string   | required                                | The article designation.                       |
     * | familyCode      | string   | required                                | The article family code.                       |
     * | subFamilyCode   | string   | nullable                                | The article sub-family code.                   |
     * | subSubFamilyCode| string   | nullable                                | The article sub-sub-family code.               |
     * | vatCode         | string   | required                                | The article VAT code.                          |
     * | vatRate         | string   | required                                | The article VAT rate.                          |
     * | first_price     | string   | required                                | The article first price.                       |
     * | second_price    | string   | required                                | The article second price.                      |
     * | has_stock       | boolean  | required_if:has_stock,1                 | The article stock status.                      |
     * | stock           | integer  | required_if:has_stock,1                 | The article stock quantity.                    |
     *
     * ### Entity Type Values
     *
     * | Type                  | Value |
     * |-----------------------|-------|
     * | Product               | 1     |
     * | Service               | 2     |
     *
     * @param Request $request The HTTP request instance containing all required data.
     * @return JsonResponse Returns a JSON response with the API result.
     * @throws GuzzleException If an error occurs during the API request.
     */
    public function putArticles(Request $request): JsonResponse{
        $request->validate([
            'code' => 'required|string',
            'designation' => 'required|string',
            'familyCode' => 'required|string',
            'subFamilyCode' => 'nullable|string',
            'subSubFamilyCode' => 'nullable|string',
            'vatCode' => 'required|string',
            'vatRate' => 'required|string',
            'first_price' => 'required|string',
            'second_price' => 'required|string',
            'stock' => 'nullable|string',
        ]);

        return response()->json($this->winmax4Service->putArticles(
            $request->id_winmax4,
            $request->code,
            $request->designation,
            $request->familyCode,
            $request->subFamilyCode,
            $request->subSubFamilyCode,
            $request->vatCode,
            $request->vatRate,
            $request->first_price,
            $request->second_price,
            $request->stock,
            $request->is_active
        ), 200);
    }

    /**
     * Delete articles from the Winmax4 API.
     *
     * This method interfaces with the Winmax4Service to delete an article specified by the given code.
     * It returns the result of the deletion operation as a JSON response with an HTTP status code of 200.
     *
     * ### Parameters
     *
     * | Parameter | Type    | Description                       |
     * |-----------|---------|-----------------------------------|
     * | `$code`   | `string`| The unique code of the article to delete. |
     *
     * ### Return
     *
     * | Type               | Description                                             |
     * |--------------------|---------------------------------------------------------|
     * | `JsonResponse`     | A JSON response containing the deletion result.         |
     * |                    | The structure of the response depends on the service implementation. |
     *
     * ### Exceptions
     *
     * | Exception                                  | Condition                                              |
     * |--------------------------------------------|--------------------------------------------------------|
     * | `Exception`                                | Throws when the deletion process encounters an error.  |
     *
     * @param string $code The unique code of the article to be deleted.
     * @return \Illuminate\Http\JsonResponse JSON response with the deletion result.
     * @throws GuzzleException If an error occurs during the HTTP request.
     */
    public function deleteArticles(string $code): JsonResponse
    {
        return response()->json($this->winmax4Service->deleteArticles($code), 200);
    }


}
