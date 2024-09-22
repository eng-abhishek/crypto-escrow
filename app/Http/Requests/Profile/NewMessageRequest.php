<?php

namespace App\Http\Requests\Profile;

use App\Conversation;
use App\Events\Message\MessageSent;
use App\Message;
use App\Rules\PgpEncryptedRule;
use Illuminate\Foundation\Http\FormRequest;

class NewMessageRequest extends FormRequest
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
            'message' => ['required','string',new PgpEncryptedRule()]
        ];
    }

    public function persist(Conversation $conversation)
    {
        
        $receiver = $conversation -> otherUser();
        if(is_null($receiver->pgp_key)){
            session() -> flash('error', 'Oop`s receiver user do not have pgp key.');
            return redirect()->back();
        }

        //dd($this->all(), $conversation);
        $sender = auth() -> user();
        $receiver = $conversation -> otherUser();

        $newMessage = new Message;

        $newMessage -> setConversation($conversation);
        $newMessage -> setSender($sender);
        $newMessage -> setReceiver($receiver);

        $newMessage -> setContent($this->message,$receiver);
        $newMessage -> save();
        // event(new MessageSent($newMessage));
    }
}
