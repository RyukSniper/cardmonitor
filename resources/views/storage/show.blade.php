@extends('layouts.app')

@section('content')

    <div class="d-flex mb-1">
        <h2 class="col mb-0"><a class="text-body" href="/storages">Lagerplatz</a><span class="d-none d-md-inline"> > {{ $model->full_name }}</span></h2>
        <div class="d-flex align-items-center">
            <a href="{{ $model->editPath }}" class="btn btn-primary" title="Bearbeiten"><i class="fas fa-edit"></i></a>
            <a href="/storages" class="btn btn-secondary ml-1">Übersicht</a>
            @if ($model->isDeletable())
                <form action="{{ $model->path }}" class="ml-1" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger" title="Löschen"><i class="fas fa-trash"></i></button>
                </form>
            @endif
        </div>
    </div>

    <div class="row align-items-stretch">

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">{{ $model->full_name }}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-label"><b>Name</b></div>
                                <div class="col-value">{{ $model->name }}</div>
                            </div>
                            @if ($model->parent)
                                <div class="row">
                                    <div class="col-label"><b>Hauptlagerplatz</b></div>
                                    <div class="col-value"><a class="text-body" href="{{ $model->parent->path }}">{{ $model->parent->name }}</a></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Artikel</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-label"><b>Artikel</b></div>
                        <div class="col-value">{{ $model->articleStats->count_formatted }}</div>
                    </div>
                    <div class="row">
                        <div class="col-label"><b>Verkaufspreis</b></div>
                        <div class="col-value">{{ $model->articleStats->price_formatted }} €</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row align-items-stretch">

        <div class="col-12 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Standard Zuordnung</div>
                <div class="card-body">
                    <storage-content-table :model="{{ json_encode($model) }}"></storage-content-table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
            @if (count($model->descendants))
                <div class="card mb-3">
                    <div class="card-header">Unterlagerplätze</div>
                    <div class="card-body">
                        @foreach ($model->descendants as $descendant)
                            <div class="">
                                <h6><a class="text-body" href="{{ $descendant->path }}">{{ $descendant->full_name }}</a></h6>
                            </div>
                            <div class="row">
                                <div class="col-label"><b>Artikel</b></div>
                                <div class="col-value">{{ $descendant->articleStats->count_formatted }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-label"><b>Verkaufspreis</b></div>
                                <div class="col-value">{{ $descendant->articleStats->price_formatted }} €</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>

@endsection