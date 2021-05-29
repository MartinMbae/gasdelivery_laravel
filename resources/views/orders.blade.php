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
                                            Order Date
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Action
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
                                            <td>{{ $latestOrders->firstItem() + $key }}</td>
                                            <td>{{ $latestOrder->user->name }}</td>
                                            <td>{{ $latestOrder->user->phone }}</td>
                                            <td>{{ $latestOrder->weight }} Kg</td>
                                            <td>{{ $latestOrder->classification }}</td>
                                            <td>{{ $latestOrder->count }}</td>
                                            <td>{{ $latestOrder->price }}</td>
                                            <td>{{ (int) $latestOrder->price * (int) $latestOrder->count }}</td>
                                            <td>{{ $latestOrder->date }}</td>
                                            <td>{{ $latestOrder->stage }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Mark As
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        <a class="dropdown-item" href="#" onclick="event.preventDefault();
                                                     document.getElementById('completed-form{{$latestOrder->id}}').submit();">Completed</a>
                                                        <a class="dropdown-item" href="#" onclick="event.preventDefault();
                                                            document.getElementById('cancel-form{{$latestOrder->id}}').submit();">Cancelled</a>
                                                    </div>

                                                    <form id="completed-form{{$latestOrder->id}}" action="{{ url('complete_order') }}" method="POST" class="d-none">
                                                        @csrf
                                                        <input type="hidden" name="order_id" value="{{$latestOrder->id}}">
                                                    </form>
                                                    <form id="cancel-form{{$latestOrder->id}}" action="{{ url('cancel_order') }}" method="POST" class="d-none">
                                                        @csrf
                                                        <input type="hidden" name="order_id" value="{{$latestOrder->id}}">
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>

                                    {{ $latestOrders->links() }}


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

