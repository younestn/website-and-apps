@php use App\Utils\Helpers;use App\Utils\ProductManager; @endphp
<div class="table-responsive d-none d-md-block">
    <table class="table align-middle table-striped">
        <tbody>
        @if($wishlists->count()>0)
            @foreach($wishlists as $key=>$wishlist)
                @php($product = $wishlist->productFullInfo)
                @if( $wishlist->productFullInfo)
                    <tr>
                        <td>
                            <div class="media gap-3 align-items-center mn-w200">
                                <div class="avatar border rounded size-3-437rem">
                                    <img class="img-fit dark-support rounded aspect-1" alt=""
                                        src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}">
                                </div>
                                <div class="media-body">
                                    <a href="{{route('product',$product['slug'])}}">
                                        <h6 class="text-truncate text-capitalize width--20ch link-hover-base">{{$product['name']}}</h6>
                                    </a>
                                </div>
                                @if($brand_setting && $product->product_type != 'digital')
                                    <div class="media-body">
                                        <h6 class="text-truncate width--10">{{$product?->brand ? $product->brand['name']:'' }} </h6>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="product__price d-flex flex-wrap align-items-end gap-2 mb-4 ">
                                <div class="text-primary d-flex gap-2 align-items-center">
                                    {!! getPriceRangeWithDiscount(product: $product) !!}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2 align-items-center">
                                <a href="javascript:"
                                   class="btn btn-outline-success rounded-circle btn-action add-to-compare compare_list-{{$product['id']}} {{ isProductInCompareList($product->id) ?'compare_list_icon_active':'' }}"
                                   data-product-id ="{{$product['id']}}" data-action="{{route('product-compare.index')}}"
                                   id="compare_list-{{$product['id']}}">
                                    <i class="bi bi-repeat"></i>
                                </a>
                                <button type="button" data-confirm-text="{{ translate('ok') }}"
                                        data-wishlist="{{ translate('wishlist') }}"
                                        data-product-id = "{{$product['id']}}"
                                        data-action="{{ route('delete-wishlist') }}"
                                        data-bs-toggle="modal" data-bs-target="#wishlist-modal-{{ $product['id'] }}"
                                        class="btn btn-outline-danger rounded-circle btn-action">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
        </tbody>
    </table>
</div>

@if($wishlists->count()==0)
    <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-3 w-100">
        <img width="80" class="mb-3" src="{{ theme_asset('assets/img/empty-state/empty-wishlist.svg') }}" alt="">
        <h5 class="text-center text-muted">
            {{ translate('You_have_not_added_product_to_wishlist_yet') }}!
        </h5>
    </div>
@endif

<div class="d-flex flex-column gap-2 d-md-none">
    @if($wishlists->count()>0)
        @foreach($wishlists as $key=>$wishlist)
            @php($product = $wishlist->productFullInfo)
            @if( $wishlist->productFullInfo)
                <div class="media gap-3 bg-light p-3 rounded">
                    <div class="avatar border rounded size-3-437rem">
                        <img
                            src="{{ getStorageImages(path:$product->thumbnail_full_url, type: 'product') }}"
                            class="img-fit dark-support rounded" alt="">
                    </div>
                    <div class="media-body d-flex flex-column gap-1">
                        <a href="{{route('product',$product['slug'])}}">
                            <h6 class="text-truncate text-capitalize width--20ch link-hover-base">{{$product['name']}}</h6>
                        </a>
                        <div>
                            {{ translate('price') }} :
                            <div class="product__price d-flex flex-wrap align-items-end gap-2 mb-4 ">
                                <div class="text-primary d-flex gap-2 align-items-center">
                                    {!! getPriceRangeWithDiscount(product: $product) !!}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 align-items-center mt-1">
                            <a href="javascript:"
                               class="btn btn-outline-success rounded-circle btn-action add-to-compare compare_list-{{$product['id']}} {{ isProductInCompareList($product->id) ?'compare_list_icon_active':'' }}"
                               data-product-id ="{{$product['id']}}" data-action="{{route('product-compare.index')}}">
                                <i class="bi bi-repeat"></i>
                            </a>
                            <button type="button"
                                    data-bs-toggle="modal" data-bs-target="#wishlist-modal-{{ $product['id'] }}"
                                    class="btn btn-outline-danger rounded-circle btn-action" >
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>

<div class="border-0">
    {{ $wishlists->links() }}
</div>

@if($wishlists->count()>0)
    @foreach($wishlists as $key=>$wishlist)
        @php($product = $wishlist->productFullInfo)
        @if( $wishlist->productFullInfo)
            <div class="modal fade" style="--bs-modal-border-radius: 20px;" id="wishlist-modal-{{ $product['id'] }}" tabindex="-1"
                 aria-labelledby="wishlist-modal-{{ $product['id'] }}Label"
                 aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <button type="button" class="btn-close bg-F2F2F2 rounded-circle" style="background-size: 12px;" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-20 py-0 mb-30">
                            <form action="{{ route('delete-wishlist') }}" method="post" class="remove-wishlist-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $product['id'] }}">
                                <div class="d-flex flex-column align-items-center text-center mb-30">
                                    <img src="{{ theme_asset('assets/img/modal/wishlist-clear.svg') }}" width="70" class="aspect-1 h-70px mb-20" id="custom-modal-1753511225620-image" alt="">
                                    <h4 class="modal-title my-3">
                                        {{ translate('do_you_want_to_clear_your_wishlist').' ?' }}
                                    </h4>
                                    <div class="text-center px-4">
                                        {{ translate('are_you_sure_you_want_to_clear_this_product_permanently_from_your_wishlist').' ?' }}
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center gap-3 mt-3">
                                    <button type="button" class="btn btn-light h-45px px-5" style="--bs-btn-bg: #f2f2f2;" data-bs-dismiss="modal">
                                        {{ translate('No') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary h-45px px-5" data-bs-dismiss="modal">
                                        {{ translate('Yes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif
