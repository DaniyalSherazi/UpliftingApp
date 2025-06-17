@extends('admin.layout.app')
@section('title', 'Vehicle Type Rates')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Vehicale Type Rate</h3>
                </div>
                <form id="form" action="{{ route('vehicle-type-rates.store') }}" class="card-body" method="Post">
                    @csrf
                    <div class="row clearfix">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Current Base Price</label>
                                <input type="text" name="current_base_price" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Current Price Per KM</label>
                                <input type="text" name="current_price_per_km" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label>Current Price Per Mint</label>
                                <input type="text" name="current_price_per_min" class="form-control">
                            </div>
                        </div>
                        <!-- <div class="col-sm-12">
                            <div class="form-group mt-2 mb-3">
                                <input type="file" class="dropify">
                                <small id="fileHelp" class="form-text text-muted">This is some placeholder block-level help
                                    text for the above input. It's a bit lighter and easily wraps to a new line.</small>
                            </div>
                        </div> -->
                        <div class="col-sm-12">
                            <div class="form-group mt-3">
                                <label>Description</label>
                                <textarea rows="4" class="form-control no-resize" name="description"
                                    placeholder="Please type what you want..."></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush