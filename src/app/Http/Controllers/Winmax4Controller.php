<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Controlink\LaravelWinmax4\app\Models\Winmax4Currency;
use Controlink\LaravelWinmax4\app\Models\Winmax4DocumentType;
use Controlink\LaravelWinmax4\app\Models\Winmax4Entity;
use Controlink\LaravelWinmax4\app\Models\Winmax4Family;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4SubFamily;
use Controlink\LaravelWinmax4\app\Models\Winmax4SyncStatus;
use Controlink\LaravelWinmax4\app\Models\Winmax4Tax;
use Controlink\LaravelWinmax4\app\Services\Winmax4Service;
use Illuminate\Http\Request;

class Winmax4Controller extends Controller
{
    protected $winmax4Service;

    /**
     * Winmax4Controller constructor.
     *
     */
    public function __construct()
    {
        $winmaxSettings = Winmax4Setting::where(config('winmax4.license_column'), session('licenseID'))->first();

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
     * Get Winmax4 settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinmax4Settings()
    {
        $winmax4 = Winmax4Setting::where(config('winmax4.license_column'), session('licenseID'))->first();

        if ($winmax4) {
            return response()->json([
                'message' => 'Success',
                'winmax4' => $winmax4,
            ], 200);
        }
    }

    /**
     * Generate token for Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function generateToken(Request $request)
    {
        $request->validate([
            'url' => 'required',
            'company_code' => 'required',
            'username' => 'required',
            'password' => 'required',
            'n_terminal' => 'required',
        ]);

        $url = $request->url;
        $company_code = $request->company_code;
        $username = $request->username;
        $password = $request->password;
        $n_terminal = $request->n_terminal;

        $type_docs_invoice = $request->type_docs_invoice;
        $type_docs_invoice_receipt = $request->type_docs_invoice_receipt;
        $type_docs_credit_note = $request->type_docs_credit_note;
        $type_docs_receipt = $request->type_docs_receipt;

        $response = $this->winmax4Service->generateToken($url, $company_code, $username, $password, $n_terminal);

        if ($response->Results[0]->Code === 'OK') {
            $winmax4 = Winmax4Setting::where(config('winmax4.license_column'), $request->sessionID)->first();

            if($winmax4) {
                $winmax4->url = $url;
                $winmax4->company_code = $company_code;
                $winmax4->username = $username;
                $winmax4->password = $password;
                $winmax4->n_terminal = $n_terminal;

                $winmax4->type_docs_invoice = $type_docs_invoice;
                $winmax4->type_docs_invoice_receipt = $type_docs_invoice_receipt;
                $winmax4->type_docs_credit_note = $type_docs_credit_note;
                $winmax4->type_docs_receipt = $type_docs_receipt;
            }else{
                $winmax4 = new Winmax4Setting();
                $winmax4->url = $url;
                $winmax4->company_code = $company_code;
                $winmax4->username = $username;
                $winmax4->password = $password;
                $winmax4->n_terminal = $n_terminal;

                $winmax4->type_docs_invoice = $type_docs_invoice;
                $winmax4->type_docs_invoice_receipt = $type_docs_invoice_receipt;
                $winmax4->type_docs_credit_note = $type_docs_credit_note;
                $winmax4->type_docs_receipt = $type_docs_receipt;

                if (config('winmax4.use_license')) {
                    $winmax4->{config('winmax4.license_column')} = $request->sessionID;
                }
            }

            $winmax4->save();

            return response()->json([
                'message' => 'Success',
                'data' => $response->Results[0]->Message,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->Results[0]->Message,
            ], 400);
        }
    }

    /**
     * Get currencies from Winmax4 API
     */
    public function getCurrencies(){
        return response()->json(Winmax4Currency::get(), 200);
    }

    /**
     * Get document types from Winmax4 API
     */
    public function getDocumentTypes(){
        return response()->json(Winmax4DocumentType::get(), 200);
    }

    /**
     * Get families from Winmax4 API
     */
    public function getFamilies(){
        return response()->json(Winmax4Family::with('subFamilies.subSubFamilies')->get(), 200);
    }

    /**
     * Get sub families from Winmax4 API
     * @param $family_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubFamilies($family_id){
        return response()->json(Winmax4Family::find($family_id)->subFamilies, 200);
    }

    /**
     * Get sub sub families from Winmax4 API
     * @param $sub_family_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubSubFamilies($sub_family_id){
        return response()->json(Winmax4SubFamily::find($sub_family_id)->subSubFamilies, 200);
    }

    /**
     * Get taxes from Winmax4 API
     */
    public function getTaxes(){
        return response()->json(Winmax4Tax::with('taxRates')->get(), 200);
    }

    /**
     * Get articles from Winmax4 API
     */
    public function getArticles(){
        //return response()->json(Winmax4Article::get(), 200);

        return $this->winmax4Service->getArticles();
    }

    /**
     * Get entities from Winmax4 API
     */
    public function getEntities(){
        return response()->json(Winmax4Entity::get(), 200);
    }

    /**
     * Post entities to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEntities(Request $request)
    {
        return response()->json($this->winmax4Service->postEntities($request->all()), 200);
    }

    /**
     * Put entities to Winmax4 API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function putEntities(Request $request)
    {
        return response()->json($this->winmax4Service->putEntities($request->all()), 200);
    }

    /**
     * Update the last synced at timestamp for the given model.
     *
     * @param string $model The model to update the last synced at timestamp for.
     * @param int $licence_id The licence id to update the last synced at timestamp for.
     */
    public function updateLastSyncedAt($model, $licence_id = null)
    {

        if (config('winmax4.use_license')) {
            Winmax4SyncStatus::updateOrCreate([
                'model' => class_basename($model),
                config('winmax4.license_column') => $licence_id,
            ],
            [
                'last_synced_at' => now(),
            ]);
        } else {
            Winmax4SyncStatus::updateOrCreate([
                'model' => class_basename($model),
            ],
            [
                'last_synced_at' => now(),
            ]);
        }
    }

}
