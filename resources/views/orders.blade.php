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
                                            Item
                                        </th>
                                        <th>
                                            Address
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Order Date
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
                                            <td>
                                                {{ $latestOrder->address }}
                                                <button class="btn btn-sm"  type="button"
                                                        data-toggle="modal" data-target="#viewFullAddress{{ $latestOrder->id }}">View Full Address</button>
                                            </td>
                                            <td>{{ $latestOrder->status }}</td>
                                            <td>{{ $latestOrder->created_at_parsed }}</td>
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
                                        <!-- Modal -->
                                        <div class="modal fade" id="viewFullAddress{{ $latestOrder->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Address</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul>
                                                            <li><b>Address :</b> {{ $latestOrder->address }} </li>
                                                            <li><b>House Number :</b> {{ $latestOrder->house_number }} </li>
                                                            <li><b>Estate :</b> {{ $latestOrder->apartment_estate }} </li>
                                                            <li><b>Landmark :</b> {{ $latestOrder->landmark }} </li>
                                                        </ul>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
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

