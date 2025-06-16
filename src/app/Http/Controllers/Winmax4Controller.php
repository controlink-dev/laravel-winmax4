<?php

namespace Controlink\LaravelWinmax4\app\Http\Controllers;

use Carbon\Carbon;
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;
use Controlink\LaravelWinmax4\app\Models\Winmax4SyncStatus;
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
     * Get Winmax4 settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinmax4Settings()
    {
        $winmax4 = Winmax4Setting::where(config('winmax4.license_column'), session(config('winmax4.license_session_key')))->first();

        if (!$winmax4) {
            return response()->json([
                'message' => 'Error',
                'error' => 'No settings found',
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'winmax4' => $winmax4,
        ], 200);
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
            'warehouse_id' => 'required',
        ]);

        $url = $request->url;
        $company_code = $request->company_code;
        $username = $request->username;
        $password = $request->password;
        $n_terminal = $request->n_terminal;

        $warehouse_id = $request->warehouse_id;

        $type_docs_invoice = $request->type_docs_invoice;
        $type_docs_invoice_receipt = $request->type_docs_invoice_receipt;
        $type_docs_credit_note = $request->type_docs_credit_note;
        $type_docs_receipt = $request->type_docs_receipt;

        $response = $this->winmax4Service->generateToken($url, $company_code, $username, $password, $n_terminal);

        if ($response->Results[0]->Code === 'OK') {
            $winmax4 = Winmax4Setting::where(config('winmax4.license_column'), $request->sessionID)->first();

            $exists = $winmax4 ? true : false;

            if($winmax4) {
                $winmax4->url = $url;
                $winmax4->company_code = $company_code;
                $winmax4->username = $username;
                $winmax4->password = $password;
                $winmax4->n_terminal = $n_terminal;

                $winmax4->warehouse_id = $warehouse_id;

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

                $winmax4->warehouse_id = $warehouse_id;

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
                'first_time' => !$exists,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->Results[0]->Message,
            ], 400);
        }
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

    /**
     * Get the last synced at timestamp for the given model.
     *
     * @param string $model The model to get the last synced at timestamp for.
     * @param int $licence_id The licence id to get the last synced at timestamp for.
     * @return string|null The last synced at timestamp for the given model.
     */
    public function getLastSyncedAt($model, $licence_id = null): Carbon {
        if (config('winmax4.use_license')) {
            $winmax4SyncStatus = Winmax4SyncStatus::where('model', class_basename($model))
                ->where(config('winmax4.license_column'), $licence_id)
                ->first();
        } else {
            $winmax4SyncStatus = Winmax4SyncStatus::where('model', class_basename($model))->first();
        }

        return $winmax4SyncStatus ? $winmax4SyncStatus->last_synced_at : Carbon::parse('2000-01-01 00:00:00');
    }

    /**
     * Get Winmax4 sync status
     * @param string $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinmax4SyncStatus($model){
        $winmax4SyncStatus = Winmax4SyncStatus::where('model', $model)->first();

        if (!$winmax4SyncStatus) {
            return response()->json([
                'message' => 'Error',
                'error' => 'No settings found',
            ], 404);
        }

        return response()->json([
            'message' => 'Success',
            'winmax4SyncStatus' => $winmax4SyncStatus,
        ], 200);
    }
}
