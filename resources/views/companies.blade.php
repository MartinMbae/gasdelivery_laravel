@extends('layout')

@section('content')


    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <span class="card-title h4 ">Gas Companies</span>
                            <div class="float-right">
                                <button class="btn-primary" data-toggle="modal" data-target="#addCompanyModal">Add New
                                    Gas Company
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class=" text-primary">
                                    <tr>
                                        <th>
                                            #
                                        </th>
                                        <th>
                                            Company Name
                                        </th>

                                        <th>
                                            Cylinder
                                        </th>

                                        <th>
                                            Action
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($companies as $key => $company)
                                        <tr>
                                            <td>
                                                {{ $key+1 }}
                                            </td>
                                            <td>
                                                {{ $company->name }}
                                            </td>
                                            <td>
                                                <img src="{{ $company->url }}" height="100" width="100">

                                            </td>
                                            <td>
                                                <i class="material-icons"  data-toggle="modal" data-target="#editCompanyModal{{ $company->id }}">edit</i>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="editCompanyModal{{ $company->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Edit Gas Company</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ url('editCompany') }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="company_name" class="col-form-label">Company Name</label>
                                                                <input type="text" class="form-control" name="name" id="company_name" value="{{ old('name') ?? $company->name }}">
                                                                <input type="hidden" name="id"  value="{{ $company->id }}">
                                                                {!! $errors->gas_edit->first('name', '<p class="text-danger">:message</p>') !!}
                                                            </div>

                                                            <div class="form-group">
                                                                <label><strong>Change Image</strong></label>
                                                                <div>
                                                                    <input type="file"  name="image" class="custom-file-input">
                                                                    <label class="custom-file-label" for="customFile">Choose file</label>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

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

    <div class="modal fade" id="addCompanyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add new Gas Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('addCompany') }}" method="POST"  enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="company_name" class="col-form-label">Company Name</label>
                            <input type="text" class="form-control" name="name" id="company_name" value="{{ old('name') }}" required>
                            {!! $errors->gas->first('name', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group">
                            <label><strong>Upload Files</strong></label>
                            <div class="custom-file">
                                <input type="file"  name="image" class="custom-file-input" id="customFile">
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @if($errors->hasBag('gas'))
        <script>
            $('#addCompanyModal').modal('show');
        </script>
    @endif

    @if($errors->hasBag('gas_edit'))

        <script>
            $('#editCompanyModal{{ old('id') }}').modal('show');
        </script>
    @endif

@endsection

