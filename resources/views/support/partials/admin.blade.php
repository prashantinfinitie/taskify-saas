<div class="card">
    <div class="card-body">
        <h5 class="card-title">Your Tickets</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ ucfirst($ticket->status) }}</td>
                        <td>{{ ucfirst($ticket->priority->name) }}</td>
                        <td>{{ $ticket->category ? $ticket->category->name : 'N/A' }}</td>
                        <td>
                            <a href="{{ route('support.show', $ticket->id) }}" class="btn btn-info btn-sm">View</a>
                            <!-- Add more actions if needed -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
