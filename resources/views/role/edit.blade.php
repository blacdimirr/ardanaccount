{{Form::model($role,array('route' => array('roles.update', $role->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('name',__('Name'),['class'=>'form-label'])}}<x-required></x-required>
                {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Role Name'), 'required'=>'required'))}}
                @error('name')
                <small class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                @if(!empty($permissions))
                    <h6 class="my-3">{{__('Assign Permission to Roles')}} </h6>
                    <table class="table  mb-0" id="dataTable-1">
                        <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input align-middle" name="checkall"  id="checkall" >
                            </th>
                            <th>{{__('Module')}} </th>
                            <th>{{__('Permissions')}} </th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $modulePrefixes = [
                                'income vs expense',
                                'loss & profit',
                                'balance sheet',
                                'trial balance',
                                'create payment',
                                'delete payment',
                                'convert invoice',
                                'convert retainer',
                                'manage',
                                'create',
                                'edit',
                                'delete',
                                'show',
                                'buy',
                                'send',
                                'income',
                                'expense',
                                'tax',
                                'invoice',
                                'bill',
                                'duplicate',
                                'ledger',
                                'contract',
                            ];
                            $permissionsByModule = [];
                            foreach($permissions as $permissionId => $permissionName)
                            {
                                $module = __('Custom');
                                $actionLabel = \Illuminate\Support\Str::title(str_replace('_', ' ', $permissionName));
                                foreach($modulePrefixes as $prefix)
                                {
                                    if(\Illuminate\Support\Str::startsWith($permissionName, $prefix . ' '))
                                    {
                                        $module = trim(substr($permissionName, strlen($prefix)));
                                        $actionLabel = \Illuminate\Support\Str::title($prefix);
                                        break;
                                    }
                                }
                                $moduleKey = \Illuminate\Support\Str::slug($module);
                                if(!isset($permissionsByModule[$moduleKey]))
                                {
                                    $permissionsByModule[$moduleKey] = [
                                        'label' => $module,
                                        'items' => [],
                                    ];
                                }
                                $permissionsByModule[$moduleKey]['items'][] = [
                                    'id' => $permissionId,
                                    'name' => $permissionName,
                                    'action' => $actionLabel,
                                ];
                            }
                        @endphp
                        @foreach($permissionsByModule as $moduleKey => $moduleGroup)
                            <tr>
                                <td><input type="checkbox" class="form-check-input align-middle ischeck"  data-id="{{ $moduleKey }}" ></td>
                                <td><label class="ischeck" data-id="{{ $moduleKey }}">{{ ucfirst($moduleGroup['label']) }}</label></td>
{{--                                <td>{{ ucfirst($module) }}</td>--}}
                                <td>
                                    <div class="row">
                                        @foreach($moduleGroup['items'] as $permissionItem)
                                            <div class="col-md-3 custom-control custom-checkbox">
                                                {{Form::checkbox('permissions[]',$permissionItem['id'],in_array($permissionItem['id'], $rolePermissions), ['class'=>'form-check-input isscheck isscheck_'.$moduleKey,'id' =>'permission'.$permissionItem['id']])}}
                                                {{Form::label('permission'.$permissionItem['id'],$permissionItem['action'],['class'=>'form-check-label'])}}<br>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{Form::close()}}


<script>
    $(document).ready(function () {
        $("#checkall").click(function(){
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
        $(".ischeck").click(function(){
            var ischeck = $(this).data('id');
            $('.isscheck_'+ ischeck).prop('checked', this.checked);
        });
    });
</script>
