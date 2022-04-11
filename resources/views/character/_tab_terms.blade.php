<p class="alert alert-warning">Notice: user-made terms are not binding, unless you have provided an electronic signature to the designer in question. You are responsible for obtaining and recording the version of the terms you agreed to.</p>
@foreach($character->image->designers as $designer)
    <div class="d-flex justify-content-between">
        <h5 class="text-capitalzie">{!! $designer->displayLink() !!}'s Terms</h5>
        {!! $designer->lastUpdatedTerms() !!}
    </div>
    {!! $designer->displayTerms() !!}
    <hr>
@endforeach
@if(Settings::get('commercial_permissions_visible'))
    <h5><i class="text-{{ $character->commercial_permissions ? 'success far fa-circle' : 'danger fas fa-times'  }} fa-fw mr-2"></i> {{ $character->commercial_permissions ? 'Has' : 'No'  }} commercial rights</h5>
@endif