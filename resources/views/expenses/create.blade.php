{{ Form::open(array('url' => 'expenses','enctype' => "multipart/form-data")) }}
<div class="row">
    <div class="form-group  col-md-6">
        {{ Form::label('category_id', __('Category')) }}
        {{ Form::select('category_id', $category,null, array('class' => 'form-control','required'=>'required')) }}
        @error('category_id')
        <span class="invalid-category_id" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('amount', __('Amount')) }}
        {{ Form::number('amount', '', array('class' => 'form-control','required'=>'required')) }}
        @error('amount')
        <span class="invalid-amount" role="alert">
        <strong class="text-danger">{{ $message }}</strong>
    </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('ncf_type_id', __('NCF Type')) }}
        {{ Form::select('ncf_type_id', $ncfTypes, null, ['class' => 'form-control']) }}
        @error('ncf_type_id')
        <span class="invalid-ncf_type_id" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('ncf_series', __('NCF Series')) }}
        {{ Form::text('ncf_series', '', ['class' => 'form-control','placeholder'=>__('Series as provided by vendor')]) }}
        @error('ncf_series')
        <span class="invalid-ncf_series" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('ncf_number', __('NCF Number')) }}
        <div class="form-icon-user">
            <span><i class="ti ti-hash"></i></span>
            {{ Form::text('ncf_number', '', ['class' => 'form-control','placeholder'=>__('Enter NCF number from vendor invoice')]) }}
        </div>
        @error('ncf_number')
        <span class="invalid-ncf_number" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('date', __('Date')) }}
        {{ Form::text('date', '', array('class' => 'form-control pc-datepicker-1','required'=>'required')) }}
        @error('date')
        <span class="invalid-date" role="alert">
        <strong class="text-danger">{{ $message }}</strong>
    </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('project_id', __('Project')) }}
        {{ Form::select('project_id', $projects,null, array('class' => 'form-control','required'=>'required')) }}
        @error('project_id')
        <span class="invalid-project_id" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('user_id', __('User')) }}
        {{ Form::select('user_id', $users,null, array('class' => 'form-control','required'=>'required')) }}
        @error('user_id')
        <span class="invalid-user_id" role="alert">
            <strong class="text-danger">{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group  col-md-6">
        {{ Form::label('attachment', __('Attachment')) }}
        {{ Form::file('attachment', array('class' => 'form-control','accept'=>'.jpeg,.jpg,.png,.doc,.pdf')) }}
        @error('attachment')
        <span class="invalid-attachment" role="alert">
        <strong class="text-danger">{{ $message }}</strong>
    </span>
        @enderror
    </div>
    <div class="form-group  col-md-12">
        {{ Form::label('description', __('Description')) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']) !!}
        @error('terms')
        <span class="invalid-terms" role="alert">
        <strong class="text-danger">{{ $message }}</strong>
    </span>
        @enderror
    </div>
    <div class="col-md-12 text-end">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Create'),array('class'=>'btn btn-primary'))}}
    </div>
</div>
{{ Form::close() }}
