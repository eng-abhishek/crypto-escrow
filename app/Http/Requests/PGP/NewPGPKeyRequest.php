<?php

namespace App\Http\Requests\PGP;

use App\Exceptions\RequestException;
use App\Exceptions\ValidationException;
use const App\Marketplace\NEW_PGP_ENCRYPTED_MESSAGE;
use const App\Marketplace\NEW_PGP_SESSION_KEY;
use const App\Marketplace\NEW_PGP_VALIDATION_NUMBER_KEY;
use App\Marketplace\PGP;
use App\Config;
use Illuminate\Foundation\Http\FormRequest;

class NewPGPKeyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth() -> check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'newpgp' => 'string'
        ];
    }

    public function messages()
    {
        return [
            'newpgp.string' => 'You must enter your new PGP key!'
        ];
    }

    public function persist()
    {
        $user = auth()->user();
        $oldKeys = $user->pgpKeys;
        if ($user->pgp_key == $this->newpgp){
            throw new RequestException('You cannot use old keys');
        }
        foreach ($oldKeys as $key){
            if ($key->key == $this->newpgp){
                throw new RequestException('You cannot use old keys');
            }
        }
        $newUsersPGP = $this->newpgp;
        // dd($newUsersPGP);
        
        //$validationNumber = Config('app.name')."_PGP_".rand(100000000000,999999999999);
        
        $validationNumber = Config('app.name')."_PGP_".rand(0,999); // Radnom number to confirm
        //dd($validationNumber);
        $decryptedMessage = "You have successfully decrypted this message.\nTo validate this key please copy validation number to the field on the site\nValidation number:". $validationNumber;
        // Encrypt throws \Exception
        try{
            $currentKey = $user->pgp_key;
            if ($currentKey == null){
                $encryptedMessage = PGP::EncryptMessage($decryptedMessage, $newUsersPGP);
            } else {
                $encryptedMessage = PGP::EncryptMessage($decryptedMessage, $currentKey);
            }
        }
        catch (\Exception $e){
            throw new RequestException($e -> getMessage());
        }

        // store data to sessions

        session() -> put(PGP::NEW_PGP_VALIDATION_NUMBER_KEY, $validationNumber );
        session() -> put(PGP::NEW_PGP_SESSION_KEY, $newUsersPGP);
        session() -> put(PGP::NEW_PGP_ENCRYPTED_MESSAGE, $encryptedMessage);

    }
}
