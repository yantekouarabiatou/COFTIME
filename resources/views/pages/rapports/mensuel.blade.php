@extends('layaout')

@section('title', $title)

@section('content')
<section class="section">
    <div class="section-header">
        <h1><i class="fas fa-chart-bar"></i> {{ $title }}</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <div class="card-header-action">
                    <form class="d-inline">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <select name="user_id" class="form-control select2">
                                    <option value="">Tous les employés</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <input type="month" name="date" class="form-control" value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary">Voir</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                @if(isset($selectedUser))
                    @include('rapports.partials.single-user', ['entries' => $dailyEntries, 'user' => $selectedUser])
                @else
                    @foreach($dailyEntries as $userId => $entries)
                        @php $user = $entries->first()->user; @endphp
                        @include('pages.rapports.partials.single-user', ['entries' => $entries, 'user' => $user])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
