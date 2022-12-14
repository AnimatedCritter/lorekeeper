@extends('admin.layout')

@section('admin-title') Loot Tables @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Loot Tables' => 'admin/data/loot-tables']) !!}

<h1>Loot Tables</h1>

<p>Loot tables can be attached to prompts as a reward for doing the prompt. This will roll a random reward from the contents of the table. Tables can be chained as well.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/loot-tables/create') }}"><i class="fas fa-plus"></i> Create New Loot Table</a></div>
@if(!count($tables))
    <p>No loot tables found.</p>
@else
    {!! $tables->render() !!}
      <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
          <div class="col-3 col-md-2 font-weight-bold">ID</div>
          <div class="col-3 col-md-4 font-weight-bold">Name</div>
          <div class="col-3 col-md-3 font-weight-bold">Display Name</div>
          <div class="col-md-2 d-none d-md-inline font-weight-bold">Disclose Drops</div>
        </div>
        @foreach($tables as $table)
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
          <div class="col-3 col-md-2 ">#{{ $table->id }}</div>
          <div class="col-3 col-md-4">{{ $table->name }}</div>
          <div class="col-3 col-md-3">{!! $table->display_name !!}</div>
          <div class="d-none d-md-inline col-md-2"><i class="far
              {{ $table->disclose_loots == 1 ? 'fa-check-circle text-success' : ($table->disclose_loots == 2 ? 'fa-pause-circle text-secondary' : 'fa-times-circle text-danger') }}
            "></i>
          </div>
          <div class="col-3 col-md-1 text-right"><a href="{{ url('admin/data/loot-tables/edit/'.$table->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
        </div>
        @endforeach
      </div>
    {!! $tables->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $tables->total() }} result{{ $tables->total() == 1 ? '' : 's' }} found.</div>
@endif

@endsection
