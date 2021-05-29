@extends('layout')

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title ">Payments</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class=" text-primary">
                                    <tr><th>
                                            #
                                        </th>
                                        <th>
                                            Phone
                                        </th>
                                        <th>
                                            Mpesa Code
                                        </th>
                                        <th>
                                           Amount
                                        </th>
                                        <th>
                                            Payment Date
                                        </th>
                                    </tr></thead>
                                    <tbody>

                                    @if(sizeof($payments) == 0)
                                        <tr>
                                            <td colspan="4">No Payments Found</td>
                                        </tr>
                                    @endif
                                    @foreach($payments as $key => $payment)
                                        <tr>
                                            <td>{{ $payments->firstItem() + $key }}</td>
                                            <td>{{ $payment->callback_phone }}</td>
                                            <td>{{ $payment->mpesa_receipt_number }}</td>
                                            <td>Ksh. {{ $payment->callback_amount }}</td>
                                            <td>{{ $payment->created_at->timezone('Africa/Nairobi')->format('dS M Y \\a\\t h:i a') }}</td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                                    {{ $payments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

