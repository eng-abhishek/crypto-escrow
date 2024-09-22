<form action="{{route('multicurrency.change')}}" method="post">
    {{csrf_field()}}

    <div class="container">
        <div class="row form-row align-center">
            <div class="col-md-4">
                <label for="current_password">Local Currency</label>
            </div>

            <div class="col-md-6">

             <select name="currency" class="form-control" style="text-align: center;">
                @foreach(\App\Marketplace\Utility\CurrencyConverter::getSupportedCurrencies() as $currency)
                <option value="{{$currency}}" @if(auth()->user()->getLocalCurrency() == $currency) selected @endif>{{$currency}}</option>
                @endforeach
            </select>

        </div>
    </div>
</div>
<button type="submit" class="btn btn-blue">Change Currency</button>
</form>
