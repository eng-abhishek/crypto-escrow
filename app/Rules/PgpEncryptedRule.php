<?php

namespace App\Rules;

use App\Marketplace\PGP;
use Illuminate\Contracts\Validation\Rule;

class PgpEncryptedRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return PGP::isPgpEncrypted($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Message must be PGP encrypted with receiver public key!';
    }
}
