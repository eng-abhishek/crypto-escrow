<?php

namespace App\Http\Requests\PGP;

use App\Exceptions\RequestException;
use App\Exceptions\ValidationException;
use App\PgpKeyChange;
use const App\Marketplace\NEW_PGP_ENCRYPTED_MESSAGE;
use const App\Marketplace\NEW_PGP_SESSION_KEY;
use const App\Marketplace\NEW_PGP_VALIDATION_NUMBER_KEY;
use App\Marketplace\PGP;
use App\PGPKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StorePGPRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'validation_number' => 'required'
        ];
    }

    public function persist()
    {
        // validation number is ok
        $correctValidationNumber = session() -> get(PGP::NEW_PGP_VALIDATION_NUMBER_KEY);
        if($correctValidationNumber == $this -> validation_number){
            try{
                // start transaction
                DB::beginTransaction();
                $user = auth()->user();
                if($user->hasPGP()) {
                    // save old pgp key
                    $savingOldKey = new PGPKey();
                    $savingOldKey->key = auth()->user()->pgp_key;
                    $savingOldKey->user_id = $user->id;
                    $savingOldKey->save();
                }
                // change users key
                $user->pgp_key = session() -> get(PGP::NEW_PGP_SESSION_KEY);
                $user->save();

                // notify admin
                $pgpKeyChange = new PgpKeyChange();
                $pgpKeyChange->user_id = $user->id;
                $pgpKeyChange->save();

                // Commit changes
                DB::commit();
            }
            catch (\Exception $e){
                DB::rollBack();
                throw new RequestException('Something went wrong, please try again!');
            }

            // forget all session data
            session() -> forget(PGP::NEW_PGP_ENCRYPTED_MESSAGE);
            session() -> forget(PGP::NEW_PGP_SESSION_KEY);
            session() -> forget(PGP::NEW_PGP_VALIDATION_NUMBER_KEY);

        }
        else{
            throw new RequestException('Your validation number is not correct!');
        }
    }
}
