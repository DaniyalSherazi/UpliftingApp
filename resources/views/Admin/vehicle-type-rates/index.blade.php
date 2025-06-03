@extends('admin.layout.app')
@section('title', 'Vehicle Type Rates')
@section('content')

    <div class="section-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between mt-3 align-items-center">
                        <a href="{{ url('admin/vehicle-type-rates/create') }}" class="btn btn-primary">Add New</a>
                        <div class="header-action d-md-flex">
                            <div class="input-group mr-2">
                                <input type="text" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section-body mt-3">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-vcenter mb-0 text-nowrap">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th class="w100">Current Base Price</th>
                                    <th class="w100">Current Price Per KM</th>
                                    <th class="w100">Current Price Per Min</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $type)
                                    @if (!empty($type) )
                                        <tr>
                                        <td>{{ $type->title }}</td>
                                        <td>{{ $type->current_base_price }}</td>
                                        <td>{{ $type->current_price_per_km }}</td>   
                                        <td><span class="text-warning">{{ $type->current_price_per_min }}</span></td>   
                                        <td></td>                                 
                                    </tr>
                                    @else
                                    <tr>
                                        <td colspan="5" class="text-center">No Riders Found</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection