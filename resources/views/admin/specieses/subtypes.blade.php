@extends('admin.layout')

@section('admin-title') Species @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Subtypes' => 'admin/data/subtypes']) !!}

<h1>Subtypes</h1>

<p>Subtypes are optional categories that can be added to species. Characters require a species, but do not require a subtype. Note that the sort order here reflects the sort order under the species name as well.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/subtypes/create') }}"><i class="fas fa-plus"></i> Create New Subtype</a></div>
@if(!count($subtypes))
    <p>No subtypes found.</p>
@else 
    <table class="table table-sm subtypes-table">
        <thead>
            <tr>
                <th>Subtype</th>
                <th>Species</th>
                <th>Free MYO Usable</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="sortable" class="sortable">
            @foreach($subtypes as $subtype)
                <tr class="sort-item" data-id="{{ $subtype->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $subtype->displayName !!}
                    </td>
                    <td>
                        {!! $subtype->species->displayName !!}
                    </td>
                    <td>
                        @if($subtype->is_free_myo_usable == 1) <i class="far fa-check-circle fa-lg text-success"></i> @else <i class="far fa-times-circle fa-lg text-danger"></i> @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/subtypes/edit/'.$subtype->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/data/subtypes/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        items: '.sort-item',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection