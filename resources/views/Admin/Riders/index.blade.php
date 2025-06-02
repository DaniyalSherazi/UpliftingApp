@extends('Admin.Layout.app')
@section('title', 'Riders')
@section('content')

    <div class="section-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-end mt-3 align-items-center">

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
                                    <th>Rider</th>
                                    <th class="w100">Email</th>
                                    <th class="w100">Phone</th>
                                    <th>Status</th>
                                    <th></th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $rider)
                                    @if (!empty($rider) )
                                        <tr>
                                        <td><img src="{{ asset($rider->avatar) }}" alt="Avatar" class="w30 rounded-circle mr-2"> <span>{{ $rider->first_name }} {{ $rider->last_name }}</span>
                                        </td>
                                        <td>{{ $rider->email }}</td>
                                        <td>{{ $rider->phone }}</td>
                                        <td>
                                            <span class="tag 
                                                    @if ($rider->status == 'pending')
                                                    tag-info
                                                    @elseif ($rider->status == 'inactive')
                                                    tag-warning
                                                    @else
                                                    tag-danger
                                                    @endif
                                                ">{{ $rider->status }}</span>
                                        </td>
                                        <td>
                                            <label class="custom-switch m-0">
                                                <input type="checkbox" value="1" class="custom-switch-input" checked>
                                                <span class="custom-switch-indicator"></span>
                                            </label>                                            
                                        </td>    
                                        <td><span class="text-warning">Medium</span></td>                                    
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