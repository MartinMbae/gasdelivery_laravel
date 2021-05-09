@extends('layout')

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <span class="card-title h4 ">Gas Cookers</span>
                            <div class="float-right">
                                <button class="btn-primary" data-toggle="modal" data-target="#addGasModal">Add New Gas
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
                                            Company
                                        </th>
                                        <th>
                                            Classification
                                        </th>
                                        <th>
                                            Weight
                                        </th>
                                        <th>
                                            Price Before Discount
                                        </th>
                                        <th>
                                            Price
                                        </th>
                                        <th>
                                            Availability
                                        </th>
                                        <th>
                                            Action
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($gasses as $key => $gas)
                                        <tr>
                                            <td>
                                                {{ $key + $gasses->firstItem() }}
                                            </td>
                                            <td>
                                                {{$gas->companyName }}
                                            </td>
                                            <td>
                                                {{$gas->classification }}
                                            </td>
                                            <td>
                                                {{$gas->weight }} Kgs
                                            </td>
                                            <td>
                                                Ksh. {{$gas->initialPrice ?? '-' }}
                                            </td>
                                            <td>
                                                Ksh. {{$gas->price }}
                                            </td>

                                            <td>
                                                {{$gas->availability }}
                                            </td>
                                            <td>
                                                <i class="material-icons"  data-toggle="modal" data-target="#editGasModal{{$gas->id}}">edit</i>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="editGasModal{{$gas->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                             aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Edit Gas</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ url('editGas') }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <input type="hidden" name="gas_id" value="{{ $gas->id }}">
                                                                <label for="recipient-name" class="col-form-label">Gas Company:</label>
                                                                <select class="form-control" name="company_id">
                                                                    <option selected disabled>Select Gas Company</option>
                                                                    @foreach($companies as $company)
                                                                        <option
                                                                            value="{{ $company->id }}" {{ old('company_id') == null ? ($company->id == $gas->company_id ? 'selected' : '') : (old('company_id') == $company->id ? 'selected' : '') }}>{{ $company->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                {!! $errors->edit_gas->first('company_id', '<p class="text-danger">:message</p>') !!}

                                                            </div>
                                                            <div class="form-group">
                                                                <label for="recipient-name" class="col-form-label">Classification</label>
                                                                <select class="form-control" name="classification">
                                                                    <option selected disabled>Select Gas Classification</option>
                                                                    @foreach($classifications as $classification)
                                                                        <option
                                                                            value="{{ $classification }}" {{  old('classification') == null ? ($classification == $gas->classification ? 'selected' : '') : ( old('classification') == $classification ? 'selected' : '') }}>{{ $classification }}</option>
                                                                    @endforeach
                                                                </select>
                                                                {!! $errors->edit_gas->first('classification', '<p class="text-danger">:message</p>') !!}

                                                            </div>
                                                            <div class="form-group">
                                                                <label class="col-form-label">Weight in KGs</label>
                                                                <input class="form-control" type="number" name="weight" min="1" max="20"
                                                                       value="{{ old('weight') ?? $gas->weight }}" step="1" required>
                                                                {!! $errors->edit_gas->first('weight', '<p class="text-danger">:message</p>') !!}

                                                            </div>

                                                            <div class="form-group">
                                                                <label class="col-form-label">Price before Discount</label>
                                                                <input class="form-control" type="number" name="initialPrice" min="100" value="{{ old('initialPrice') ?? $gas->initialPrice }}"
                                                                       placeholder="Leave blank if no discount">
                                                                {!! $errors->edit_gas->first('initialPrice', '<p class="text-danger">:message</p>') !!}

                                                            </div>

                                                            <div class="form-group">
                                                                <label class="col-form-label">Price</label>
                                                                <input class="form-control" type="number" min="100" name="price" value="{{ old('price') ?? $gas->price }}">
                                                                {!! $errors->edit_gas->first('price', '<p class="text-danger">:message</p>') !!}

                                                            </div>
                                                            <div class="form-group">
                                                                <label for="recipient-name" class="col-form-label">Availability</label>
                                                                <select class="form-control" name="availability">
                                                                    <option selected disabled>Select Gas Classification</option>
                                                                    @foreach($availability as $available)
                                                                        <option
                                                                            value="{{ $available }}" {{  old('availability') == null ? ($available == $gas->availability ? 'selected' : '') : ( old('availability') == $available ? 'selected' : '') }}>{{ $available }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="div float-right">
                                {{$gasses->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addGasModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add new Gas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('addGas') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Gas Company:</label>
                            <select class="form-control" name="company_id">
                                <option selected disabled>Select Gas Company</option>
                                @foreach($companies as $company)
                                    <option
                                        value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Classification</label>
                            <select class="form-control" name="classification">
                                <option selected disabled>Select Gas Classification</option>
                                @foreach($classifications as $classification)
                                    <option
                                        value="{{ $classification }}" {{ old('classification') == $classification ? 'selected' : '' }}>{{ $classification }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Weight in KGs</label>
                            <input class="form-control" type="number" name="weight" min="1" max="20"
                                   value="{{ old('weight') }}" step="1" required>
                            {!! $errors->add_gas->first('weight', '<p class="text-danger">:message</p>') !!}

                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Price before Discount</label>
                            <input class="form-control" type="number" name="initialPrice" min="100" value="{{ old('initialPrice') }}"
                                   placeholder="Leave blank if no discount">
                            {!! $errors->add_gas->first('initialPrice', '<p class="text-danger">:message</p>') !!}

                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Price</label>
                            <input class="form-control" type="number" min="100" name="price" value="{{ old('price') }}">
                            {!! $errors->add_gas->first('price', '<p class="text-danger">:message</p>') !!}
                        </div>

                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Availability</label>
                            <select class="form-control" name="availability">
                                @foreach($availability as $available)
                                    <option
                                        value="{{ $available }}" {{ old('availability') == $available ? 'selected' : '' }}>{{ $available }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Gas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->hasBag('add_gas'))
        <script>
            $('#addGasModal').modal('show');
        </script>
    @endif
    @if($errors->hasBag('edit_gas'))
        <script>
            $('#editGasModal{{ old('gas_id') }}').modal('show');
        </script>
    @endif
@endsection


