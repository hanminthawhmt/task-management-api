<h2>You are invited to join a project</h2>

<p>
You have been invited to join project:
<strong>{{ $invitation->project->name }}</strong>
</p>

<p>Role: {{ $invitation->role->name }}</p>

<a href="{{ url('/api/invitations/accept/'.$invitation->token) }}">
Accept Invitation
</a>

<br><br>

<a href="{{ url('/api/invitations/decline/'.$invitation->token) }}">
Decline Invitation
</a>
