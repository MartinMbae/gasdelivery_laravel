@extends('layout')

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title ">Users</h4>
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
                                            Email
                                        </th>
                                        <th>
                                            Orders Made
                                        </th>
                                    </tr></thead>
                                    <tbody>
                                    @foreach($users as $key=>$user)
                                        <tr>
                                            <td>
                                                {{ $key + 1 }}
                                            </td>
                                            <td>
                                                {{ $user->name }}
                                            </td>
                                            <td>
                                                {{ $user->phone }}
                                            </td>
                                            <td>
                                                {{ $user->email }}
                                            </td>
                                            <td class="text-success">
                                                {{ $user->orders_count }}
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
    </div>
@endsection

