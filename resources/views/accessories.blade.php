@extends('layout')

@section('content')


    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <span class="card-title h4 ">Gas Accessories</span>
                            <div class="float-right">
                                <button class="btn-primary" data-toggle="modal" data-target="#addCompanyModal">Add New
                                    Gas Accessory
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
                                            Title
                                        </th>

                                        <th>
                                            Image
                                        </th>

                                        <th>
                                            Description
                                        </th>
                                        <th>
                                            Price Before Discount
                                        </th>
                                        <th>
                                            Price
                                        </th>
                                        <th>
                                            Action
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($accessories as $key => $accessory)
                                        <tr>
                                            <td>
                                                {{ $key+1 }}
                                            </td>
                                            <td>
                                                {{ $accessory->title }}
                                            </td>
                                            <td>
                                                <img src="{{ $accessory->url }}" height="100"
                                                     width="100">
                                            </td>
                                            <td>
                                                {{ $accessory->description }}
                                            </td>
                                            <td>
                                                Ksh {{ $accessory->initialPrice }}
                                            </td>
                                            <td>
                                                Ksh {{ $accessory->price }}
                                            </td>
                                            <td>
                                                <i class="material-icons" data-toggle="modal"
                                                   data-target="#editCompanyModal{{ $accessory->id }}">edit</i>
                                                <i class="material-icons" data-toggle="modal"
                                                   data-target="#deleteAccessoryModal{{ $accessory->id }}">delete</i>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="deleteAccessoryModal{{ $accessory->id }}"
                                             tabindex="-1"
                                             role="dialog" aria-labelledby="exampleModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Delete Gas
                                                            Accessory</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ url('deleteAccessory') }}" method="POST"
                                                          enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Are you sure you would like to delete this item</p>

                                                            <input type="hidden" class="form-control" name="id"
                                                                   value="{{ $accessory->id }}" required>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Cancel
                                                            </button>
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="editCompanyModal{{ $accessory->id }}" tabindex="-1"
                                             role="dialog" aria-labelledby="exampleModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Edit Gas
                                                            Company</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ url('editAccessory') }}" method="POST"
                                                          enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="col-form-label">Accessory Name</label>
                                                                <input type="hidden" class="form-control" name="id"
                                                                       value="{{ $accessory->id }}" required>
                                                                <input type="text" class="form-control" name="title"
                                                                       value="{{ $accessory->title }}" required>
                                                                {!! $errors->add->first('title', '<p class="text-danger">:message</p>') !!}
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="col-form-label">Accessory
                                                                    Description</label>
                                                                <input type="text" class="form-control"
                                                                       name="description"
                                                                       value="{{ $accessory->description }}"
                                                                       required>
                                                                {!! $errors->add->first('description', '<p class="text-danger">:message</p>') !!}
                                                            </div>


                                                            <div class="form-group">
                                                                <label class="col-form-label">Price before
                                                                    Discount</label>
                                                                <input class="form-control" type="number"
                                                                       name="initialPrice"
                                                                       value="{{ $accessory->initialPrice }}"
                                                                       placeholder="Leave blank if no discount">
                                                                {!! $errors->add->first('initialPrice', '<p class="text-danger">:message</p>') !!}

                                                            </div>

                                                            <div class="form-group">
                                                                <label class="col-form-label">Price</label>
                                                                <input class="form-control" type="number"
                                                                       name="price" value="{{ $accessory->price }}"
                                                                       required>
                                                                {!! $errors->add->first('price', '<p class="text-danger">:message</p>') !!}
                                                            </div>

                                                            <div class="form-group">
                                                                <label><strong>Upload Files</strong></label>
                                                                <div class="custom-file">
                                                                    <input type="file" name="image"
                                                                           class="custom-file-input" id="customFile">
                                                                    <label class="custom-file-label" for="customFile">Choose
                                                                        file</label>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Cancel
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">Edit</button>
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
                    <h5 class="modal-title" id="exampleModalLabel">Add new Gas Accessory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('addAccessory') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-form-label">Accessory Name</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                            {!! $errors->add->first('title', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Accessory Description</label>
                            <input type="text" class="form-control" name="description" value="{{ old('description') }}"
                                   required>
                            {!! $errors->add->first('description', '<p class="text-danger">:message</p>') !!}
                        </div>


                        <div class="form-group">
                            <label class="col-form-label">Price before Discount</label>
                            <input class="form-control" type="number" name="initialPrice"
                                   value="{{ old('initialPrice') }}"
                                   placeholder="Leave blank if no discount">
                            {!! $errors->add->first('initialPrice', '<p class="text-danger">:message</p>') !!}

                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Price</label>
                            <input class="form-control" type="number" name="price" value="{{ old('price') }}"
                                   required>
                            {!! $errors->add->first('price', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group">
                            <label><strong>Upload Files</strong></label>
                            <div class="custom-file">
                                <input type="file" name="image" class="custom-file-input" id="customFile">
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

