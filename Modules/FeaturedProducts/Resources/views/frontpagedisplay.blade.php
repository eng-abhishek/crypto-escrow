
<div class="row mt-5" >
    <div class="col">
        <div class="card">
            <div class="card-header  text-center" style="background-color: #488562;">
                <span class="btn btn-warning active">Featured Listings</span>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($featuredProducts as $product)
                        <div class="col-md-3 my-md-0 my-2 col-12">


                            <div class="card">
                                <div class="card-header bg-info text-center">
                                    <a href="{{ route('product.show', $product) }}" class="text-white"><marquee scrollamount="2">
                                        {{ $product -> name }}
                                    </marquee></a>
                                </div>
                                <div class="card-body text-center">
                                    <span class="badge badge-primary mb-2">{{ $product->getLocalPriceFrom() }} {{ \App\Marketplace\Utility\CurrencyConverter::getLocalSymbol() }}</span>
                                    <img class="img-thumbnail" src="{{ asset('storage/'  . $product -> frontImage() -> image) }}" alt="{{ $product -> name }}">
                                    <span class="badge badge-success">{{ $product -> user -> username }}</span><a href="{{ route('vendor.show', $product -> user) }}"> <span class="badge badge-info">View store</span></a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>