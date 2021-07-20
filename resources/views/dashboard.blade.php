@extends('layout')

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header card-header-warning card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">content_copy</i>
                            </div>
                            <p class="card-category">Total Users</p>
                            <h3 class="card-title">{{ $usersCount }}
                            </h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">
                                <a href="{{ url('users') }}">View Users</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header card-header-success card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">store</i>
                            </div>
                            <p class="card-category">Complete Orders</p>
                            <h3 class="card-title">{{ $completeOrders }}</h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">
                                <a href="{{ url('orders/completed') }}">View Complete Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header card-header-info card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">info_outline</i>
                            </div>
                            <p class="card-category">Ongoing Orders</p>
                            <h3 class="card-title">{{ $ongoingOrders }}</h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">
                                <a href="{{ url('orders/ongoing') }}">View Ongoing Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-header card-header-danger card-header-icon">
                            <div class="card-icon">
                                <i class="material-icons">info_outline</i>
                            </div>
                            <p class="card-category">Cancelled Orders</p>
                            <h3 class="card-title">{{ $cancelledOrders }}</h3>
                        </div>
                        <div class="card-footer">
                            <div class="stats">
                                <a href="{{ url('orders/cancelled') }}">View Cancelled Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title">Latest Registered Users</h4>
                            <p class="card-category">10 latest users</p>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead class="text-warning">
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                </thead>
                                <tbody>

                                @foreach($latestUsers as $key=>$latestUser)
                                    <tr>
                                        <td>{{ $key + 1}}</td>
                                        <td>{{ $latestUser->name }}</td>
                                        <td>{{ $latestUser->phone }}</td>
                                        <td>{{ $latestUser->email }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title">Latest Orders</h4>
                            <p class="card-category">10 latest orders</p>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead class="text-warning">
                                <th>#</th>
                                <th>Name</th>
                                <th>Items</th>
                                </thead>
                                <tbody>

                                @foreach($latestOrders as $key => $latestOrder)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $latestOrder->user->name }}</td>

                                        <td>
                                            <ul>
                                                @foreach($latestOrder->gasItemsOrders as $gasItemsOrder)
                                                    <li>
                                                        {{ "$gasItemsOrder->count $gasItemsOrder->company_name  Gas ($gasItemsOrder->classification) for Ksh. $gasItemsOrder->total_price" }}
                                                    </li>
                                                @endforeach
                                                @foreach($latestOrder->accessoryItemsOrders as $accessoryItemsOrder)
                                                    <li>
                                                        {{ "$accessoryItemsOrder->count ".$accessoryItemsOrder->accessory->title . ' for Ksh.'. $accessoryItemsOrder->total_price }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
