@extends('home.layout')

@section('home-title') Free MYO @endsection

@section('home-content')
    {!! breadcrumbs(['Characters' => 'characters', 'My MYO Slots' => 'myos', 'Create Free MYO' => 'new']) !!}

<h1>
        Create Free MYO 
</h1>


{!! Form::open(['url' => 'characters/myos/new', 'id' => 'submissionForm']) !!}
    {{ Form::hidden('name', 'Free MYO') }}
    {{ Form::hidden('user_id', Auth::user()->id) }}
    {{ Form::hidden('owner_url', null) }}
    {{ Form::hidden('description', null) }}
    {{ Form::hidden('is_visible', 1) }}
    {{ Form::hidden('is_giftable', 1) }}
    {{ Form::hidden('is_tradeable', 1) }}
    {{ Form::hidden('is_sellable', null) }}
    {{ Form::hidden('designer_id[]', null) }}
    {{ Form::hidden('designer_url[]', null) }}
    {{ Form::hidden('artist_id[]', null) }}
    {{ Form::hidden('artist_url[]', null) }}
    {{ Form::hidden('species_id', null) }}
    {{ Form::hidden('subtype_id', null) }}
    {{ Form::hidden('rarity_id', null) }}
    {{ Form::hidden('feature_id[]', null) }}
    {{ Form::hidden('feature_data[]', null) }}
    <div class="text-center">
        <a href="#" class="btn btn-primary" id="submitButton">Create Free MYO</a>
    </div>
{!! Form::close() !!}
@endsection

@section('scripts')
@parent 
    <script>
        $(document).ready(function() {
            var $submitButton = $('#submitButton');
            var $confirmationModal = $('#confirmationModal');
            var $formSubmit = $('#formSubmit');
            var $submissionForm = $('#submissionForm');
            
            $submitButton.on('click', function(e) {
                e.preventDefault();
                $confirmationModal.modal('show');
            });

            $formSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.submit();
            });
        });
    </script>
@endsection