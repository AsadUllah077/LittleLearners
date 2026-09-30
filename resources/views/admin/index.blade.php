<x-layout :title="ucfirst($resource)"><a class="back-link" href="{{ route('admin.dashboard') }}">← Learning studio</a><div class="section-heading"><h1>{{ ucfirst(str_replace('-',' ',$resource)) }}</h1>
@unless(in_array($resource,['users','children']))<a class="button primary" href="{{ route('admin.create',$resource) }}">+ Create</a>
@endunless</div><form method="get" class="search-form"><input name="q" placeholder="Search by name" aria-label="Search" value="{{ request('q') }}"><button class="button secondary">Search</button></form><div class="panel table-scroll"><table><thead><tr><th>Name</th><th>Status / detail</th><th>Actions</th></tr></thead><tbody>
@forelse($records as $record)<tr><td>{{ $record->$column }}</td><td>{{ $record->email ?? $record->learning_level ?? ($record->is_active === null?'Learning item':($record->is_active?'Active':'Inactive')) }}</td><td><div class="actions"><a href="{{ route('admin.edit',[$resource,$record->id]) }}">Edit</a>
@unless(in_array($resource,['users','children']))<form method="post" action="{{ route('admin.destroy',[$resource,$record->id]) }}" data-confirm="Delete this content and its related records?">
@csrf @method('delete')<button class="text-button danger-text">Delete</button></form>
@endunless</div></td></tr>
@empty<tr><td colspan="3">No matching records.</td></tr>
@endforelse</tbody></table>{{ $records->links() }}</div></x-layout>
