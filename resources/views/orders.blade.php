@extends('layout')

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title ">Orders</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class=" text-primary">
                                    <tr><th>
                                            #
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                           Phone
                                        </th>
                                        <th>
                                            Weight
                                        </th>
                                        <th>
                                            Service
                                        </th>
                                        <td>
                                            Quantity
                                        </td>
                                        <th>
                                            Amount
                                        </th>
                                        <th>
                                           Total
                                        </th>
                                        <th>
                                            Date
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                    </tr></thead>
                                    <tbody>

                                    @if(sizeof($latestOrders) == 0)
                                        <tr>
                                            <td colspan="4">No Orders found for the selected category</td>
                                        </tr>
                                    @endif
                                    @foreach($latestOrders as $key => $latestOrder)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $latestOrder->user->name }}</td>
                                            <td>{{ $latestOrder->user->phone }}</td>
                                            <td>{{ $latestOrder->weight }} Kg</td>
                                            <td>{{ $latestOrder->classification }}</td>
                                            <td>{{ $latestOrder->count }}</td>
                                            <td>{{ $latestOrder->price }}</td>
                                            <td>{{ (int) $latestOrder->price * (int) $latestOrder->count }}</td>
                                            <td>{{ $latestOrder->date }}</td>
                                            <td>{{ $latestOrder->stage }}</td>
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
    </div>
@endsection

