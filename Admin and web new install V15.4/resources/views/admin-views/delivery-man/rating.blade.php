@extends('layouts.admin.app')

@section('title', translate('delivery_Man_Review'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-10 mb-3">
            <div class="">
                <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/deliveryman.png')}}" width="20" alt="">
                    {{$deliveryMan['f_name']. ' '. $deliveryMan['l_name']}}
                </h2>
            </div>

            <div class="d-flex justify-content-end flex-wrap gap-10">
                <a href="{{ route('admin.delivery-man.list') }}" class="btn btn-primary gap-1">
                    <i class="fi fi-rr-angle-left fs-10"></i> {{translate('back')}}
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body my-3">
                <div class="row align-items-md-center gx-md-5">
                    <div class="col-md-auto mb-3 mb-md-0">
                        <div class="d-flex align-items-center">
                            <img
                                class="avatar avatar-xxl avatar-4by3 {{Session::get('direction') === "rtl" ? 'ms-4' : 'me-4'}}"
                                src="{{ getStorageImages(path:$deliveryMan->image_full_url , type: 'backend-basic') }}"
                                alt="{{translate('image_description')}}">
                            <div class="d-block">
                                <h4 class="display-4 fw-semibold text-dark mb-0">
                                    {{number_format($averageRating, 2, '.', ' ')}}
                                </h4>
                                <p> {{translate('of')}} {{$reviews->count()?? 0}} {{translate('reviews')}}
                                    <span
                                        class="badge badge-soft-dark badge-pill {{Session::get('direction') === "rtl" ? 'me-1' : 'ms-1'}}"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md">
                        <ul class="list-unstyled list-unstyled-py-2 mb-0">
                            <li class="d-flex align-items-center gap-3">
                                <span>{{translate('5')}} {{ translate('star') }}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($five/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($five/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span>{{$five}}</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <span>{{translate('4')}} {{ translate('star') }}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($four/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($four/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span>{{$four}}</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <span>{{translate('3')}} {{ translate('star') }}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($three/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($three/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span>{{$three}}</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <span>{{translate('2')}} {{ translate('star') }}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($two/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($two/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span>{{$two}}</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <span>{{translate('1')}} {{ translate('star') }}</span>
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{$total==0?0:($one/$total)*100}}%;"
                                         aria-valuenow="{{$total==0?0:($one/$total)*100}}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span>{{$one}}</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <div class="flex-grow-1 max-w-280">
                        <form action="{{ url()->current() }}" method="GET">
                            <div class="input-group flex-grow-1 max-w-280">
                                <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                           placeholder="{{ translate('search_by_Order_ID') }}"
                                           aria-label="Search orders" value="{{ request('searchValue') }}" required>
                                <div class="input-group-append search-submit">
                                    <button type="submit">
                                        <i class="fi fi-rr-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <form action="{{ url()->current() }}" method="GET">
                    <div class="row gy-3 align-items-end">

                        <div class="col-xl-3 col-sm-6">
                            <div>
                                <label for="from" class="form-label">{{ translate('from') }}</label>
                                <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                                       class="form-control"
                                       title="{{ translate('from_date') }}">
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div>
                                <label for="to_date" class="form-label">{{ translate('to') }}</label>
                                <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                                       class="form-control"
                                       title="{{ ucfirst(translate('to_date')) }}">
                            </div>
                        </div>

                        <div class="col-xl-3 col-sm-6">
                            <div class="select-wrapper">
                                <select class="form-select" name="rating">
                                    <option value="" selected>
                                        --{{ translate('select_Rating') }}--
                                    </option>
                                    <option
                                        value="1" {{ request('rating') == 1 ? 'selected': '' }}>{{ translate('1') }}</option>
                                    <option
                                        value="2" {{ request('rating') == 2 ? 'selected': '' }}>{{ translate('2') }}</option>
                                    <option
                                        value="3" {{ request('rating') == 3 ? 'selected': '' }}>{{ translate('3') }}</option>
                                    <option
                                        value="4" {{ request('rating') == 4 ? 'selected': '' }}>{{ translate('4') }}</option>
                                    <option
                                        value="5" {{ request('rating') == 5 ? 'selected': '' }}>{{ translate('5') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <button id="filter" type="submit" class="btn btn-primary flex-grow-1 filter">
                                    <i class="fi fi-rr-bars-filter fs-10"></i>
                                    {{ translate('filter') }}
                                </button>
                                <a href="{{ url()->current() }}" class="btn btn-secondary flex-grow-1">
                                    {{ translate('reset') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless align-middle w-100">
                    <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('order_ID')}}</th>
                        <th>{{translate('reviewer')}}</th>
                        <th>{{translate('review')}}</th>
                        <th>{{translate('date')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($reviews as $key=> $review)
                        <tr>
                            <td>
                                {{$reviews->firstItem()+$key}}
                            </td>
                            <td>
                                <a class="text-dark text-hover-primary"
                                   href="{{ $review->order_id ? route('admin.orders.details',['id'=>$review->order_id]) : ''}}">{{ $review->order_id }}</a>
                            </td>
                            <td>
                                <a class="d-flex align-items-center gap-3"
                                   href="{{route('admin.customer.view',[$review['customer_id']])}}">
                                    <div class="avatar avatar-circle">
                                        <img
                                            class="avatar-img"
                                            src="{{ getStorageImages(path:$review?->customer->image_full_url, type: 'backend-basic') }}"
                                            alt="{{ ('image_description')}}">
                                    </div>
                                    <div>
                                    <span class="d-block h4 text-hover-primary mb-0">{{$review?->customer['f_name']." ".$review?->customer['l_name']}} <i
                                            class="fi fi-sr-badge-check text-primary fs-12" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="{{ translate('Verified_Customer') }}"></i></span>
                                        <span
                                            class="d-block fs-14 text-body">{{$review?->customer->email??""}}</span>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div class="text-wrap">
                                    <div class="d-flex mb-2">
                                        <label class="badge text-bg-info badge-info">
                                            <span>{{$review->rating }} <i class="fi fi-sr-star fs-10"></i> </span>
                                        </label>
                                    </div>
                                    <div class="content p-0">
                                        @if(strlen($review['comment']) > 200))
                                            {{ substr($review['comment'], 0, 200) }}
                                            <span id="show-more-{{$review->id}}" data-id="{{$review->id}}" class="toggle-btn">...<a href="javascript:">{{ translate('show_more') }}</a></span>
                                            <span id="show-more-content-{{$review->id}}" class="show-more-content">
                                            {{substr($review['comment'], 200)}}
                                            <span id="show-less-{{$review->id}}" data-id="{{$review->id}}" class="toggle-btn"><a href="javascript:">{{ translate('show_less') }}</a></span>
                                        @else
                                            {{ $review['comment'] }}
                                        @endif
                                    </div>

                                </div>
                            </td>
                            <td>
                                {{date('d M Y H:i:s',strtotime($review['updated_at']))}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {{ $reviews->links() }}
                </div>
            </div>
            @if(count($reviews)==0)
                @include('layouts.admin.partials._empty-state',['text'=>'no_review_found'],['image'=>'default'])
            @endif
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/deliveryman.js')}}"></script>
@endpush
