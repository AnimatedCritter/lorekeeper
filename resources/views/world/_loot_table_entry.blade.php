<div class="row world-entry">
    <div class="col-12">
        <h3>{!! $name !!}</h3>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th width="70%">Loot</th>
                    <th width="30%">Weight</th>
                </tr>
            </thead>
            <tbody>
                @foreach($table->loot as $loot)
                    <tr>
                        <td>
                            {!! $loot->displayName !!}
                        </td>
                        <td>{{ $loot->weight }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>