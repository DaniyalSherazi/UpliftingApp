@extends('Admin.Layout.app')
@section('title', 'Customers')
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
                                    <th>customer</th>
                                    <th class="w100">Email</th>
                                    <th class="w100">Phone</th>
                                    <th>Status</th>
                                    <th></th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $customer)
                                    @if (!empty($customer) )
                                        <tr>
                                        <td><img src="{{ asset($customer->avatar) }}" alt="Avatar" class="w30 rounded-circle mr-2"> <span>{{ $customer->first_name }} {{ $customer->last_name }}</span>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>
                                            <span class="tag 
                                                    @if ($customer->status == 'pending')
                                                    tag-info
                                                    @elseif ($customer->status == 'inactive')
                                                    tag-warning
                                                    @else
                                                    tag-danger
                                                    @endif
                                                ">{{ $customer->status }}</span>
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
                                        <td colspan="5" class="text-center">No Customers Found</td>
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
    <div class="section-body">
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <a href="templateshub.net">Templates Hub</a>
                    </div>
                    <div class="col-md-6 col-sm-12 text-md-right">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item"><a href="doc/index.html">Documentation</a></li>
                            <li class="list-inline-item"><a href="javascript:void(0)">FAQ</a></li>
                            <li class="list-inline-item"><a href="javascript:void(0)"
                                    class="btn btn-outline-primary btn-icon">Buy Now</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection