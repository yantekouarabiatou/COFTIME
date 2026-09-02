{{-- pages/dossiers/partials/actions.blade.php --}}
<a href="{{ route('dossiers.show', $dossier) }}" class="btn btn-sm btn-info">
    <i class="fas fa-eye"></i>
</a>

@if($canEdit)
    <a href="{{ route('dossiers.edit', $dossier) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i>
    </a>
    <button class="btn btn-sm btn-danger delete-dossier-btn"
            data-url="{{ route('dossiers.destroy', $dossier) }}">
        <i class="fas fa-trash"></i>
    </button>
@endif
